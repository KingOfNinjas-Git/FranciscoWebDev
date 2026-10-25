<?php
// Plan 6: leads are saved, confirmation emails, admin inbox.
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
$good = ['name' => 'Ana Silva', 'email' => 'ana@example.com', 'project_type' => 'business', 'description' => 'I need a site for my bakery.', 'style' => 'warm colours', 'features' => ['seo', 'multilang'], 'budget' => 'medium', 'deadline' => 'June'];

echo "== a valid brief is saved and emailed\n";
$r = $send('POST', '/contact', $good + ['_ft' => $agedToken(10), '_locale' => 'pt']);
$lead = Lead::first();
$check('redirects back to the form with the success flag', $r->getStatusCode() === 302 && str_contains((string) $r->headers->get('Location'), '/pt#contact'));
$check('lead stored with every field', $lead && $lead->name === 'Ana Silva' && $lead->email === 'ana@example.com' && $lead->project_type === 'business' && $lead->features === ['seo', 'multilang'] && $lead->budget === 'medium' && $lead->deadline === 'June' && $lead->style === 'warm colours');
$check('lead remembers the language, starts as new, email marked as sent', $lead->locale === 'pt' && $lead->status === 'new' && $lead->mail_sent === true && $lead->mail_error === null);
$all = $mails();
$check('two emails: one to me, one confirmation', count($all) === 2);
$toMe = $all[0]->getOriginalMessage(); $toHer = $all[1]->getOriginalMessage();
$check('notification goes to the contact address with the admin link', $toMe->getTo()[0]->getAddress() === config('portfolio.contact_email') && str_contains($toMe->getTextBody(), 'In the admin: https://franciscowebdev.com/admin/leads/' . $lead->id) && $toMe->getReplyTo()[0]->getAddress() === 'ana@example.com');
$check('confirmation goes to the sender, in Portuguese, with a copy of the brief', $toHer->getTo()[0]->getAddress() === 'ana@example.com' && str_contains($toHer->getSubject(), 'Recebi o teu briefing') && str_contains($toHer->getTextBody(), 'Olá Ana Silva') && str_contains($toHer->getTextBody(), 'I need a site for my bakery.') && str_contains($toHer->getTextBody(), 'Website institucional'), $toHer->getTextBody());
$check('confirmation replies go to me', $toHer->getReplyTo()[0]->getAddress() === config('portfolio.contact_email'));

echo "== things that must not create leads\n";
Cache::flush();
$n = Lead::count();
$send('POST', '/contact', $good + ['_ft' => $agedToken(10), 'website' => 'http://spam']);
$send('POST', '/contact', $good + ['_ft' => '123456789.' . str_repeat('a', 64)]);
$send('POST', '/contact', ['_ft' => $agedToken(10), 'name' => '', 'email' => 'x']);
$send('POST', '/contact', $good + ['_ft' => $agedToken(7300)]);
$check('honeypot, forged token, invalid input and expired token save nothing', Lead::count() === $n);
$send('POST', '/contact', array_merge($good, ['description' => 'buy http://a.com http://b.com https://c.com now', '_ft' => $agedToken(10)]));
$check('link-stuffed message is dropped', Lead::count() === $n);
Cache::flush();
$send('POST', '/contact', array_merge($good, ['description' => 'one link https://example.com only', '_ft' => $agedToken(10)]));
$check('a message with a single link is fine', Lead::count() === $n + 1);

echo "== email failure never loses a lead\n";
Cache::flush();
config(['mail.default' => 'failing', 'mail.mailers.failing' => ['transport' => 'smtp', 'host' => '127.0.0.1', 'port' => 1, 'timeout' => 1]]);
app('mail.manager')->forgetMailers();
$before = Lead::count();
$r = $send('POST', '/contact', array_merge($good, ['name' => 'Bruno', 'email' => 'bruno@example.com', '_ft' => $agedToken(10)]));
$bruno = Lead::where('name', 'Bruno')->first();
$check('the brief is saved and the visitor sees success', Lead::count() === $before + 1 && $bruno && $bruno->mail_sent === false && ! empty($bruno->mail_error) && $r->getStatusCode() === 302);
config(['mail.default' => 'array']); app('mail.manager')->forgetMailers();

echo "== no leads table yet (before the migration)\n";
Schema::drop('leads');
Cache::flush();
$sentBefore = count($mails());
$r = $send('POST', '/contact', $good + ['_ft' => $agedToken(10)]);
$check('the form still works: email goes out, no crash', $r->getStatusCode() === 302 && count($mails()) > $sentBefore);
$check('admin badge falls back to zero', Lead::newCount() === 0);
Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%leads%')->delete();
Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
Cache::flush();
$send('POST', '/contact', array_merge($good, ['name' => 'Carla', '_ft' => $agedToken(10)]));
Lead::where('name', 'Carla')->update(['status' => 'new']);
Lead::create(['name' => 'Bruno', 'email' => 'bruno@example.com', 'project_type' => 'webapp', 'description' => 'App', 'mail_sent' => false, 'mail_error' => 'Connection refused']);

echo "== admin inbox\n";
$check('leads pages need the admin login', in_array($send('GET', '/admin/leads')->getStatusCode(), [302, 301]) && str_contains((string) $send('GET', '/admin/leads')->headers->get('Location'), 'login'));
$r = $send('GET', '/admin/leads', [], true); $h = $r->getContent();
$check('list shows the leads and the count', $r->getStatusCode() === 200 && str_contains($h, 'Carla') && str_contains($h, 'All &middot;'));
$check('nav shows a badge with the number of new leads', preg_match('/aria-label="(\d+) new"/', $h, $m) && (int) $m[1] === Lead::where('status', 'new')->count());
$id = Lead::where('name', 'Carla')->value('id');
$r = $send('GET', "/admin/leads/$id", [], true); $h = $r->getContent();
$check('detail shows the full brief and a reply button', $r->getStatusCode() === 200 && str_contains($h, 'I need a site for my bakery.') && str_contains($h, 'Reply by email') && str_contains($h, 'Business / company website'));
$r = $send('PUT', "/admin/leads/$id", ['status' => 'won', 'notes' => 'Quote sent'], true);
$check('status and private notes can be saved', Lead::find($id)->status === 'won' && Lead::find($id)->notes === 'Quote sent');
$r = $send('PUT', "/admin/leads/$id", ['status' => 'hacked'], true);
$check('unknown status is rejected', Lead::find($id)->status === 'won');
$r = $send('GET', '/admin/leads?status=won', [], true);
$check('filter by status', str_contains($r->getContent(), 'Carla') && ! str_contains($r->getContent(), 'Bruno'));
$bid = Lead::where('name', 'Bruno')->value('id');
$h = $send('GET', "/admin/leads/$bid", [], true)->getContent();
$check('a lead whose email failed shows a warning', str_contains($h, 'did not go out'));
$h = $send('GET', '/admin/leads', [], true)->getContent();
$check('the list flags it too', str_contains($h, 'Email not sent'));
$send('DELETE', "/admin/leads/$id", [], true);
$check('delete works', Lead::find($id) === null);
$check('missing lead is a 404', $send('GET', '/admin/leads/9999', [], true)->getStatusCode() === 404);

echo "== privacy notice mentions the stored brief\n";
foreach (['/privacy' => 'saved in the database', '/pt/privacy' => 'guardado na base de dados', '/es/privacy' => 'guarda en la base de datos'] as $u => $needle) {
    $check("$u says the brief is stored", str_contains($send('GET', $u)->getContent(), $needle));
}

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
