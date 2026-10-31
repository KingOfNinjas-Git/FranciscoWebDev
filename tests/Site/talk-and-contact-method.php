<?php
// "We talk first" section and the "How would you like to talk?" field in the contact form.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\{Cache, Schema}; use App\Models\Lead;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$send = function (string $method, string $path, array $data = [], bool $admin = false) use ($kernel, $app) {
    app('cookie')->flushQueuedCookies();
    $app['session']->driver()->flush();
    $req = Request::create('https://franciscowebdev.com' . $path, $method, $data);
    if ($admin) { $req->setLaravelSession($app['session']->driver()); $req->session()->put('is_admin', true); $req->session()->put('admin_last_seen', time()); $req->session()->start(); $req->session()->put('is_admin', true); $req->session()->put('admin_last_seen', time()); $req->request->set('_token', $req->session()->token()); }
    return $kernel->handle($req);
};
$agedToken = function (int $age) { $t = time() - $age; $ref = new ReflectionMethod(App\Support\FormToken::class, 'sign'); return $t . '.' . $ref->invoke(null, $t); };
$mails = fn () => app('mail.manager')->mailer('array')->getSymfonyTransport()->messages();
$home = $send('GET', '/')->getContent();
echo "== the section\n";
$check('"We talk first" sits between projects and services', ($p = strpos($home, 'id="projects"')) !== false && ($t = strpos($home, 'id="talk"')) !== false && ($s = strpos($home, 'id="services"')) !== false && $p < $t && $t < $s);
$check('it says there is no price list', str_contains($home, 'there is no price list'));
$check('three ways to talk and a button to the form', substr_count(substr($home, $t, $s - $t), '<li class="flex items-start gap-4">') === 3 && str_contains(substr($home, $t, $s - $t), 'href="#contact"'));
$check('same section in Portuguese and Spanish', collect(['pt' => 'Falamos primeiro', 'es' => 'Hablamos primero'])->every(fn ($needle, $l) => str_contains($send('GET', "/$l")->getContent(), $needle)));
$check('translation keys exist in every language', collect(['en', 'pt', 'es'])->every(fn ($l) => count(trans('site.talk.ways', [], $l)) === 3 && count(trans('site.contact.methods', [], $l)) === 4 && trans('site.talk.title', [], $l) !== 'site.talk.title'));

echo "== the form\n";
$check('has the "how would you like to talk" choice with four options', str_contains($home, 'name="contact_method"') && substr_count($home, '<option value="inperson"') + substr_count($home, '<option value="video"') + substr_count($home, '<option value="whatsapp"') + substr_count($home, '<option value="email"') === 4);
$check('has no budget field any more, but the deadline stays', ! str_contains($home, 'name="budget"') && str_contains($home, 'name="deadline"'));

echo "== sending it\n";
$good = ['name' => 'Rui', 'email' => 'rui@example.com', 'project_type' => 'business', 'description' => 'A site for my garage.', 'contact_method' => 'inperson'];
$send('POST', '/contact', $good + ['_ft' => $agedToken(10), '_locale' => 'en']);
$lead = Lead::first();
$check('the choice is saved with the lead', $lead && $lead->contact_method === 'inperson');
$mine = $mails()[0]->getOriginalMessage()->getTextBody();
$check('the email to me says how they want to talk', str_contains($mine, 'Preferred way to talk: inperson'));
$check('the confirmation to the sender repeats it in their language', str_contains($mails()[1]->getOriginalMessage()->getTextBody(), 'How would you like to talk?: In person (anywhere in Portugal)'));
$check('the admin page for the lead shows it', str_contains($send('GET', '/admin/leads/' . $lead->id, [], true)->getContent(), 'In person (anywhere in Portugal)'));
$r = $send('POST', '/contact', ['name' => 'Bad', 'email' => 'bad@example.com', 'project_type' => 'business', 'description' => 'x', 'contact_method' => 'carrier-pigeon', '_ft' => $agedToken(10), '_locale' => 'en']);
$check('an unknown choice is refused and no second lead is created', Lead::count() === 1);
$send('POST', '/contact', ['name' => 'Old', 'email' => 'old@example.com', 'project_type' => 'business', 'description' => 'Still works without it.', 'budget' => 'medium', '_ft' => $agedToken(10), '_locale' => 'en']);
$check('a brief without the choice (or with an old budget value) still works', Lead::count() === 2 && Lead::latest('id')->first()->contact_method === null);

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
