<?php
// Gemini provider checks on a throwaway in-memory database, Google's API faked.
chdir($argv[1]);
foreach (['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'GEMINI_API_KEY' => 'test-gemini-key', 'ANTHROPIC_API_KEY' => '', 'AI_PROVIDER' => '', 'AI_GEMINI_MODEL' => 'gemini-3.8-flash', 'APP_URL' => 'http://localhost'] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Support\Facades\Http; use Illuminate\Http\Request; use App\Models\AiGeneration;
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> $extra" : '') . "\n"; $ok ? $pass++ : $fail++; };
$fake = function (array $stubs) { Http::swap(new Illuminate\Http\Client\Factory()); Http::fake($stubs); };

$check('provider auto-detected as gemini from GEMINI_API_KEY', config('ai.provider') === 'gemini' && config('ai.model') === 'gemini-3.8-flash' && config('ai.key_name') === 'GEMINI_API_KEY');
$check('container resolves GeminiClient', app(App\Services\Ai\LlmClient::class) instanceof App\Services\Ai\GeminiClient);

$gOk = fn ($text, $extra = []) => Http::response(array_merge(['candidates' => [['content' => ['role' => 'model', 'parts' => [['text' => 'thinking...', 'thought' => true], ['text' => $text]]], 'finishReason' => 'STOP']],
    'usageMetadata' => ['promptTokenCount' => 900, 'candidatesTokenCount' => 400, 'thoughtsTokenCount' => 100], 'modelVersion' => 'gemini-3.8-flash'], $extra), 200);
$schema = ['type' => 'object', 'properties' => ['a' => ['type' => 'string'], 'b' => ['type' => 'object', 'properties' => ['c' => ['type' => 'string']], 'required' => ['c'], 'additionalProperties' => false]], 'required' => ['a', 'b'], 'additionalProperties' => false];
$client = app(App\Services\Ai\LlmClient::class);
$url = 'generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent';

$fake([$url => $gOk('{"a":"hi","b":{"c":"x"}}')]);
$r = $client->structured('SYS', 'USER', $schema);
$check('parses answer, skips thought part', $r['data']['a'] === 'hi' && $r['model'] === 'gemini-3.8-flash');
$check('usage: input + output(+thinking)', $r['input_tokens'] === 900 && $r['output_tokens'] === 500, json_encode($r));
Http::assertSent(function ($req) {
    $b = $req->data();
    return $req->header('x-goog-api-key')[0] === 'test-gemini-key'
        && $b['systemInstruction']['parts'][0]['text'] === 'SYS'
        && $b['contents'][0]['parts'][0]['text'] === 'USER'
        && $b['generationConfig']['responseMimeType'] === 'application/json'
        && $b['generationConfig']['maxOutputTokens'] === 16000
        && isset($b['generationConfig']['responseJsonSchema']['properties']['a'])
        && ($b['generationConfig']['thinkingConfig']['thinkingLevel'] ?? '') === 'low';
});
$check('request shape: key header, system, JSON mode, full schema', true);

// Schema rejected -> falls back to the older schema format without additionalProperties, then to plain JSON mode
$seen = [];
$fake([$url => function ($req) use (&$seen, $gOk) {
    $g = $req->data()['generationConfig'];
    $seen[] = isset($g['responseJsonSchema']) ? 'json-schema' : (isset($g['responseSchema']) ? 'openapi-schema' : 'plain');
    if (isset($g['responseJsonSchema'])) return Http::response(['error' => ['message' => 'Unknown field responseJsonSchema']], 400);
    if (isset($g['responseSchema'])) {
        $hasAdditional = str_contains(json_encode($g['responseSchema']), 'additionalProperties');
        return Http::response(['error' => ['message' => $hasAdditional ? 'additionalProperties unsupported' : 'bad']], 400);
    }
    return $gOk('{"a":"plain","b":{"c":"y"}}');
}]);
$r = $client->structured('S', 'U', $schema);
$check('falls back through schema formats to plain JSON mode', $seen === ['json-schema', 'openapi-schema', 'plain'] && $r['data']['a'] === 'plain', json_encode($seen));
Http::assertSent(fn ($req) => !isset($req->data()['generationConfig']['responseJsonSchema']) && !isset($req->data()['generationConfig']['responseSchema']) && str_contains($req->data()['contents'][0]['parts'][0]['text'], 'JSON Schema'));
$check('plain mode adds the schema to the prompt', true);

// Model rejects the thinking setting -> same request is retried without it
$sent = [];
$fake([$url => function ($req) use (&$sent, $gOk) {
    $g = $req->data()['generationConfig'];
    $sent[] = isset($g['thinkingConfig']) ? 'with-thinking' : 'no-thinking';
    return isset($g['thinkingConfig']) ? Http::response(['error' => ['message' => 'Unknown name "thinkingConfig": Cannot find field.']], 400) : $gOk('{"a":"ok","b":{"c":"1"}}');
}]);
$r = $client->structured('s', 'u', $schema);
$check('retries without thinkingConfig when rejected', $sent === ['with-thinking', 'no-thinking'] && $r['data']['a'] === 'ok', json_encode($sent));
config(['ai.gemini_thinking' => '']);
$sent = [];
$fake([$url => function ($req) use (&$sent, $gOk) { $sent[] = isset($req->data()['generationConfig']['thinkingConfig']) ? 'with' : 'without'; return $gOk('{"a":"ok","b":{"c":"1"}}'); }]);
$client->structured('s', 'u', $schema);
$check('empty AI_GEMINI_THINKING sends no thinking setting', $sent === ['without'], json_encode($sent));
config(['ai.gemini_thinking' => 'low']);

$fake([$url => $gOk("```json\n{\"a\":\"fenced\",\"b\":{\"c\":\"z\"}}\n```")]);
$check('strips ```json fences', $client->structured('s', 'u', $schema)['data']['a'] === 'fenced');

foreach ([[401, 'rejected'], [403, 'rejected'], [404, 'not found'], [400, 'rejected']] as [$code, $needle]) {
    $fake([$url => Http::response(['error' => ['message' => $code === 400 ? 'API key not valid. Please pass a valid API key.' : 'x']], $code)]);
    try { $client->structured('s', 'u', $schema); $check("HTTP $code throws", false); }
    catch (App\Services\Ai\AiException $e) { $check("HTTP $code -> friendly error", str_contains(strtolower($e->getMessage()), $needle), $e->getMessage()); }
}
$fake([$url => Http::response(['error' => ['message' => 'quota']], 429, ['retry-after' => '1'])]);
try { $client->structured('s', 'u', $schema); $check('429 throws', false); } catch (App\Services\Ai\AiException $e) { $check('429 -> free-tier limit message (after retries)', str_contains($e->getMessage(), 'usage limit'), $e->getMessage()); }
$fake([$url => Http::sequence()->push(['error' => ['message' => 'busy']], 503)->push(['candidates' => [['content' => ['parts' => [['text' => '{"a":"after retry","b":{"c":"1"}}']]], 'finishReason' => 'STOP']], 'usageMetadata' => []], 200)]);
$check('retries a 503 then succeeds', $client->structured('s', 'u', $schema)['data']['a'] === 'after retry');
$fake([$url => Http::response(['promptFeedback' => ['blockReason' => 'SAFETY']], 200)]);
try { $client->structured('s', 'u', $schema); $check('blocked prompt throws', false); } catch (App\Services\Ai\AiException $e) { $check('blocked prompt -> declined message', str_contains($e->getMessage(), 'declined')); }
$fake([$url => $gOk('{"a":"x', [])]);
Http::swap(new Illuminate\Http\Client\Factory()); Http::fake([$url => Http::response(['candidates' => [['content' => ['parts' => [['text' => '{"a":"x']]], 'finishReason' => 'MAX_TOKENS']]], 200)]);
try { $client->structured('s', 'u', $schema); $check('MAX_TOKENS throws', false); } catch (App\Services\Ai\AiException $e) { $check('MAX_TOKENS -> cut off message', str_contains($e->getMessage(), 'cut off')); }
$fake([$url => $gOk('totally not json')]);
try { $client->structured('s', 'u', $schema); $check('bad JSON throws', false); } catch (App\Services\Ai\AiException $e) { $check('bad JSON -> friendly error', str_contains($e->getMessage(), 'format')); }
config(['ai.api_key' => null]);
try { $client->structured('s', 'u', $schema); $check('missing key throws', false); } catch (App\Services\Ai\AiException $e) { $check('missing key -> names GEMINI_API_KEY', str_contains($e->getMessage(), 'GEMINI_API_KEY')); }
config(['ai.api_key' => 'test-gemini-key']);

echo "== full draft + translate flow through the Gemini provider\n";
$draft = ['title' => 'Gallery a11y', 'excerpt' => 'How', 'tags' => ['A11y'], 'body_markdown' => '## Body', 'notes_for_author' => ''];
$fake([$url => $gOk(json_encode($draft))]);
$ctl = $app->make(App\Http\Controllers\AiController::class);
$mk = function ($data) use ($app) { $req = Request::create('/x', 'POST', $data); $req->setLaravelSession($app['session']->driver()); $app->instance('request', $req); return $req; };
$id = $ctl->draft($mk(['topic' => 'A long enough topic for the draft', 'type' => 'tutorial', 'tone' => 'friendly', 'length' => 'short']))->getData()->id;
app(App\Services\Ai\AiWriter::class)->run(AiGeneration::find($id));
$st = $ctl->status($id)->getData(true);
$check('draft job done via Gemini', $st['status'] === 'done' && $st['result']['title'] === 'Gallery a11y' && $st['model'] === 'gemini-3.8-flash' && $st['input_tokens'] === 900, json_encode($st));
Http::assertSent(fn ($req) => str_contains($req->url(), 'generativelanguage') && str_contains($req->data()['contents'][0]['parts'][0]['text'], '<site_context>') && str_contains($req->data()['systemInstruction']['parts'][0]['text'], 'Never invent facts'));
$check('site context + rules sent to Gemini', true);
$fake([$url => $gOk(json_encode(['pt' => ['title' => 'T', 'excerpt' => 'E', 'body_markdown' => 'B'], 'es' => ['title' => 'T2', 'excerpt' => 'E2', 'body_markdown' => 'B2']]))]);
$id = $ctl->translate($mk(['title' => 'T', 'excerpt' => 'E', 'body' => 'Body', 'locales' => ['pt', 'es']]))->getData()->id;
app(App\Services\Ai\AiWriter::class)->run(AiGeneration::find($id));
$check('translate job done via Gemini', $ctl->status($id)->getData(true)['result']['es']['title'] === 'T2');
$fake([$url => Http::response(['error' => ['message' => 'x']], 403)]);
$id = $ctl->draft($mk(['topic' => 'A long enough topic for the draft', 'type' => 'update', 'tone' => 'concise', 'length' => 'short']))->getData()->id;
app(App\Services\Ai\AiWriter::class)->run(AiGeneration::find($id));
$check('Gemini failure -> failed job with message', $ctl->status($id)->getData(true)['status'] === 'failed');
config(['ai.api_key' => null]);
$r = $ctl->draft($mk(['topic' => 'A long enough topic for the draft', 'type' => 'update', 'tone' => 'concise', 'length' => 'short']));
$check('no key -> 503 naming GEMINI_API_KEY', $r->getStatusCode() === 503 && str_contains($r->getData()->message, 'GEMINI_API_KEY'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);

