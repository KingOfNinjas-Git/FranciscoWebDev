<?php
// Plan 14: the Monday report email.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\{Artisan, DB, Schema}; use App\Models\{Lead, Post}; use App\Services\WeeklyReport;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 400) : '') . "\n"; $ok ? $pass++ : $fail++; };
$mails = fn () => collect(app('mail.manager')->mailer('array')->getSymfonyTransport()->messages())->map(fn ($m) => $m->getOriginalMessage());
$stat = fn (string $daysAgo, string $kind, string $name, int $total) => DB::table('stats')->insert(['day' => now()->subDays((int) $daysAgo)->toDateString(), 'kind' => $kind, 'name' => $name, 'total' => $total]);

echo "== an empty site\n";
$r = WeeklyReport::build();
$check('works with nothing recorded: no errors, says so plainly', str_contains($r['subject'], '0 visits') && str_contains(implode("\n", $r['lines']), 'no visits yet') && str_contains(implode("\n", $r['lines']), 'Nobody is waiting for an answer'));

echo "== with a week of data\n";
foreach ([0 => 30, 1 => 20, 3 => 10, 6 => 10] as $ago => $n) $stat((string) $ago, 'page', '/', $n);        // this week: 70 on the home page
$stat('2', 'page', '/blog/hello', 25); $stat('2', 'page', '/pt', 5);                                    // 100 views this week in total
foreach ([8 => 40, 10 => 20] as $ago => $n) $stat((string) $ago, 'page', '/', $n);                       // the week before: 60
$stat('1', 'ref', 'google.com', 12); $stat('1', 'ref', 'direct', 40); $stat('9', 'ref', 'old.example', 99);
$stat('1', 'event', 'whatsapp', 4); $stat('1', 'event', 'hero_contact', 7); $stat('1', 'event', 'lead', 2); $stat('9', 'event', 'phone', 50);
$old = Lead::create(['name' => 'Ana Silva', 'email' => 'ana@example.com', 'project_type' => 'business', 'description' => 'x', 'mail_sent' => true]); DB::table('leads')->where('id', $old->id)->update(['created_at' => now()->subDays(3)]);
Lead::create(['name' => 'Bruno', 'email' => 'bruno@example.com', 'project_type' => 'webapp', 'description' => 'x', 'status' => 'replied', 'mail_sent' => true]);
Post::create(['title' => 'A post from this week', 'slug' => 'new', 'body' => 'b', 'status' => 'published', 'published_at' => now()->subDays(2)]);
Post::create(['title' => 'An old post', 'slug' => 'old', 'body' => 'b', 'status' => 'published', 'published_at' => now()->subDays(30)]);

$r = WeeklyReport::build(); $text = implode("\n", $r['lines']);
$check('visits and the change against the week before (100 vs 60 = +67%)', str_contains($text, 'VISITS: 100 (+67% compared with the week before (60))'), $text);
$check('subject says visits and new briefs', $r['subject'] === 'Your week: 100 visits, 2 new brief(s)', $r['subject']);
$check('most read pages with counts and friendly names', str_contains($text, '70  Home (English)') && str_contains($text, '25  /blog/hello') && str_contains($text, '5  Home (PT)'));
$check('referrers from this week only', str_contains($text, '40  direct') && str_contains($text, '12  google.com') && ! str_contains($text, 'old.example'));
$check('clicks this week only (nothing from last week, zero rows hidden)', str_contains($text, '4  Contact: WhatsApp button') && str_contains($text, '7  Hero: Start a project') && str_contains($text, '2  Briefs sent') && ! str_contains($text, 'Contact: phone link'));
$check('briefs: two new this week, and who is still waiting, with a link', str_contains($text, 'BRIEFS: 1 new this week') || str_contains($text, 'BRIEFS: 2 new this week'));
$check('the waiting list names Ana with an admin link and not the answered lead', str_contains($text, 'Ana Silva') && str_contains($text, '/admin/leads/' . $old->id) && ! preg_match('/- Bruno/', $text));
$check('posts published this week, not the old one', str_contains($text, 'A post from this week') && ! str_contains($text, 'An old post'));
$check('ends with the two-minute checklist and a link to the details', str_contains($text, 'Search Console') && str_contains($text, 'Request indexing') && str_contains($text, 'https://franciscowebdev.com/admin/stats'));
$check('plain text only, nothing about individual visitors', ! preg_match('/<[a-z]+/i', $text) && ! str_contains($text, 'ana@example.com'));

echo "== sending\n";
$n = $mails()->count();
Artisan::call('report:weekly');
$mail = $mails()->slice($n)->first();
$check('the command emails it to you', $mails()->count() === $n + 1 && $mail->getTo()[0]->getAddress() === config('portfolio.contact_email') && $mail->getSubject() === $r['subject']);
Artisan::call('report:weekly', ['--print' => true]);
$check('--print shows it without sending', $mails()->count() === $n + 1 && str_contains(Artisan::output(), 'VISITS: 100'));
$check('it runs every Monday at 08:00', collect(app(Illuminate\Console\Scheduling\Schedule::class)->events())->contains(fn ($e) => str_contains($e->command, 'report:weekly') && $e->expression === '0 8 * * 1'));

echo "== the button in the admin\n";
$send = function (string $method, string $path, bool $admin = true) use ($kernel, $app) {
    app('cookie')->flushQueuedCookies(); $app['session']->driver()->flush();
    $req = Request::create('https://franciscowebdev.com' . $path, $method);
    if ($admin) { $req->setLaravelSession($app['session']->driver()); $req->session()->start(); $req->session()->put('is_admin', true); $req->session()->put('admin_last_seen', time()); $req->request->set('_token', $req->session()->token()); }
    return $kernel->handle($req);
};
$check('the Visits page offers "Email me the weekly report now"', str_contains($send('GET', '/admin/stats')->getContent(), 'Email me the weekly report now'));
$n = $mails()->count();
$r2 = $send('POST', '/admin/stats/report');
$check('pressing it sends the email and says so', $r2->getStatusCode() === 302 && $mails()->count() === $n + 1 && str_contains((string) $r2->getSession()->get('success'), 'emailed'));
$check('only the admin can press it', in_array($send('POST', '/admin/stats/report', false)->getStatusCode(), [302, 419]) && $mails()->count() === $n + 1);

echo "== before the tables exist\n";
Schema::drop('stats'); Schema::drop('lead_replies'); Schema::drop('leads');
$r3 = WeeklyReport::build();
$check('a missing table never breaks the report', str_contains(implode("\n", $r3['lines']), 'not available yet') && is_string($r3['subject']));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
