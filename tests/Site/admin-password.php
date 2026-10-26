<?php
// Plan 11: stored (hashed) admin password, change/forget/rescue, sign out everywhere, sign-in and wrong-password emails.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'database', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'ADMIN_PASSWORD' => 'correct-horse-battery', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => '', 'SESSION_SECURE_COOKIE' => 'false', 'SESSION_COOKIE' => 'francisco-cavaco-session'] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\{Artisan, Cache, DB}; use App\Services\{AdminPassword, TwoFactor}; use App\Support\Totp;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };

// two "browsers", each with its own cookies
$jars = ['A' => [], 'B' => []];
$send = function (string $method, string $path, array $data = [], string $who = 'A', bool $resetLimits = true) use ($kernel, &$jars) {
    if ($resetLimits) Cache::flush();                  // this test signs in far more often than a person would
    app('session')->driver()->flush();                 // every real request is a fresh PHP process: nothing carries over in memory
    $handler = app('session')->driver()->getHandler(); if (method_exists($handler, 'setExists')) $handler->setExists(false);
    app('cookie')->flushQueuedCookies();
    $req = Request::create('https://franciscowebdev.com' . $path, $method, $data, $jars[$who], [], ['REMOTE_ADDR' => '203.0.113.7', 'HTTP_USER_AGENT' => 'TestBrowser/1.0 (Windows)']);
    $res = $kernel->handle($req);
    app('cookie')->flushQueuedCookies();
    foreach ($res->headers->getCookies() as $c) { if ($c->isCleared()) unset($jars[$who][$c->getName()]); else $jars[$who][$c->getName()] = $c->getValue(); }
    return $res;
};
$token = fn (string $page, string $who = 'A') => preg_match('/name="_token" value="([^"]+)"/', $send('GET', $page, [], $who, false)->getContent(), $m) ? $m[1] : '';
$login = fn (string $pw, string $who = 'A', bool $resetLimits = true) => $send('POST', '/admin/login', ['_token' => $token('/admin/login', $who), 'password' => $pw], $who, $resetLimits);
$inAdmin = fn (string $who) => $send('GET', '/admin/posts', [], $who)->getStatusCode() === 200;
$mails = fn () => collect(app('mail.manager')->mailer('array')->getSymfonyTransport()->messages())->map(fn ($m) => $m->getOriginalMessage());
$subjects = fn () => $mails()->map(fn ($m) => $m->getSubject())->all();
$passwords = app(AdminPassword::class);

echo "== sign in with the .env password\n";
$r = $login('correct-horse-battery', 'A');
$check('the .env password opens the admin (as before)', str_contains((string) $r->headers->get('Location'), '/admin/projects') && $inAdmin('A'));
$check('password is still "in .env" (nothing stored yet)', ! $passwords->stored() && DB::table('admin_settings')->where('key', 'password_hash')->count() === 0);
$mail = $mails()->first(fn ($m) => str_contains($m->getSubject(), 'Admin sign-in'));
$check('you get an email: time, IP address and browser', $mail && $mail->getTo()[0]->getAddress() === config('portfolio.contact_email') && str_contains($mail->getTextBody(), '203.0.113.7') && str_contains($mail->getTextBody(), 'TestBrowser/1.0') && str_contains($mail->getTextBody(), 'Change the password') === false && str_contains($mail->getTextBody(), 'Security'));
$check('the email never contains a password', ! str_contains($mail->getTextBody() ?? '', 'correct-horse-battery'));
$check('the sign-in is listed on the Security page', str_contains($send('GET', '/admin/security', [], 'A')->getContent(), 'TestBrowser/1.0') && count($passwords->recentLogins()) === 1);

echo "== wrong passwords\n";
Cache::flush(); $before = count($subjects());
$login('nope-1', 'B', false); $login('nope-2', 'B', false);
$check('one or two mistakes send no email', count($subjects()) === $before);
$r = $login('nope-3', 'B', false);
$check('the third wrong password sends one alert', $r->getStatusCode() === 302 && collect($subjects())->filter(fn ($s) => str_contains($s, 'Wrong admin passwords'))->count() === 1);
$login('nope-4', 'B', false); $login('nope-5', 'B', false);
$check('...and only one per hour', collect($subjects())->filter(fn ($s) => str_contains($s, 'Wrong admin passwords'))->count() === 1);
$check('wrong passwords never open the admin', ! $inAdmin('B'));
Cache::flush();

echo "== change the password\n";
$r = $send('POST', '/admin/security/password', ['_token' => $token('/admin/security'), 'current_password' => 'not-my-password', 'password' => 'a-brand-new-passphrase', 'password_confirmation' => 'a-brand-new-passphrase']);
$check('a wrong current password changes nothing', ! $passwords->stored() && str_contains($send('GET', '/admin/security')->getContent() . json_encode(session()->all()), 'not right') || ! $passwords->stored());
$send('POST', '/admin/security/password', ['_token' => $token('/admin/security'), 'current_password' => 'correct-horse-battery', 'password' => 'short', 'password_confirmation' => 'short']);
$check('too short is refused', ! $passwords->stored());
$send('POST', '/admin/security/password', ['_token' => $token('/admin/security'), 'current_password' => 'correct-horse-battery', 'password' => 'a-brand-new-passphrase', 'password_confirmation' => 'different-passphrase-1']);
$check('two different entries are refused', ! $passwords->stored());
$send('POST', '/admin/security/password', ['_token' => $token('/admin/security'), 'current_password' => 'correct-horse-battery', 'password' => 'correct-horse-battery', 'password_confirmation' => 'correct-horse-battery']);
$check('the same password again is refused', ! $passwords->stored());

$login('correct-horse-battery', 'B'); // a second browser signs in
$check('browser B is signed in too', $inAdmin('B'));
$epochBefore = $passwords->epoch();
$r = $send('POST', '/admin/security/password', ['_token' => $token('/admin/security', 'A'), 'current_password' => 'correct-horse-battery', 'password' => 'a-brand-new-passphrase', 'password_confirmation' => 'a-brand-new-passphrase'], 'A');
$stored = DB::table('admin_settings')->where('key', 'password_hash')->value('value');
$check('a good new password is saved as a hash, never as text', $passwords->stored() && str_starts_with((string) $stored, '$2y$') && ! str_contains((string) $stored, 'a-brand-new') && ! str_contains(json_encode(DB::table('admin_settings')->get()), 'a-brand-new-passphrase'));
$check('this browser stays signed in, the other one is signed out', $inAdmin('A') && ! $inAdmin('B') && $passwords->epoch() === $epochBefore + 1);
$check('an email says the password was changed', collect($subjects())->contains(fn ($s) => str_contains($s, 'password changed')));
$send('POST', '/admin/logout', ['_token' => $token('/admin/security')], 'A');
$login('correct-horse-battery', 'A');
$check('the old .env password stops working', ! $inAdmin('A'));
$login('a-brand-new-passphrase', 'A');
$check('the new password works', $inAdmin('A'));
$check('the Security page now says it is stored safely', str_contains($send('GET', '/admin/security', [], 'A')->getContent(), 'Saved safely'));

echo "== two-factor still comes first\n";
$tf = app(TwoFactor::class); $secret = $tf->newSecret(); $tf->confirmSetup($secret, Totp::codeForStep($secret, Totp::currentStep()));
$send('POST', '/admin/logout', ['_token' => $token('/admin/security')], 'A');
$r = $login('a-brand-new-passphrase', 'A');
$check('right password then goes to the phone code, not into the admin', str_contains((string) $r->headers->get('Location'), '/admin/two-factor') && ! $inAdmin('A'));
$r = $send('POST', '/admin/two-factor', ['_token' => $token('/admin/two-factor'), 'code' => Totp::codeForStep($secret, Totp::currentStep() + 1)], 'A');
$check('the code completes the sign-in (and it is emailed)', $inAdmin('A') && collect($subjects())->filter(fn ($s) => str_contains($s, 'Admin sign-in'))->count() >= 3);
$tf->disable();

echo "== sign out everywhere\n";
$login('a-brand-new-passphrase', 'B');
$check('both browsers are in', $inAdmin('A') && $inAdmin('B'));
$send('POST', '/admin/security/logout-everywhere', ['_token' => $token('/admin/security', 'A')], 'A');
$check('both are signed out, including the one that pressed it', ! $inAdmin('A') && ! $inAdmin('B'));
$check('an email says so', collect($subjects())->contains(fn ($s) => str_contains($s, 'signed out everywhere')));
$login('a-brand-new-passphrase', 'A');
$check('signing in again works', $inAdmin('A'));

echo "== the rescue command\n";
$login('a-brand-new-passphrase', 'B');
Artisan::call('admin:reset-password');
$check('the command says what it did', str_contains(Artisan::output(), 'ADMIN_PASSWORD'));
$check('every browser is signed out', ! $inAdmin('A') && ! $inAdmin('B'));
$login('a-brand-new-passphrase', 'A');
$check('the saved password no longer works', ! $inAdmin('A'));
$login('correct-horse-battery', 'A');
$check('the .env password works again', $inAdmin('A') && ! $passwords->stored());

echo "== the Security page\n";
$h = $send('GET', '/admin/security', [], 'A')->getContent();
$check('shows the change form, sign out everywhere and recent sign-ins', str_contains($h, 'Change password') && str_contains($h, 'Sign out everywhere') && str_contains($h, 'Recent sign-ins') && str_contains($h, 'In .env'));
$check('the login page is untouched and the admin stays closed without it', str_contains($send('GET', '/admin/login', [], 'B')->getContent(), 'password') && $send('GET', '/admin/security', [], 'B')->getStatusCode() === 302);

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
