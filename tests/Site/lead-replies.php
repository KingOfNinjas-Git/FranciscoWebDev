<?php
// Plan 13: reply to a lead from the admin (templates, history), reminders for unanswered leads, WhatsApp/phone/email buttons.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\{Artisan, Cache, Schema}; use App\Models\{Lead, LeadReply, Project};
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$send = function (string $method, string $path, array $data = [], bool $admin = true) use ($kernel, $app) {
    app('cookie')->flushQueuedCookies();
    $app['session']->driver()->flush();
    $req = Request::create('https://franciscowebdev.com' . $path, $method, $data);
    if ($admin) { $req->setLaravelSession($app['session']->driver()); $req->session()->start(); $req->session()->put('is_admin', true); $req->session()->put('admin_last_seen', time()); $req->request->set('_token', $req->session()->token()); }
    return $kernel->handle($req);
};
$mails = fn () => collect(app('mail.manager')->mailer('array')->getSymfonyTransport()->messages())->map(fn ($m) => $m->getOriginalMessage());

$pt = Lead::create(['name' => 'Ana Silva', 'email' => 'ana@example.com', 'project_type' => 'business', 'description' => 'Preciso de um site para a minha padaria.', 'locale' => 'pt', 'mail_sent' => true]);
$en = Lead::create(['name' => 'John', 'email' => 'john@example.com', 'project_type' => 'webapp', 'description' => 'A dashboard.', 'locale' => 'en', 'mail_sent' => true]);

echo "== templates\n";
$h = $send('GET', '/admin/leads/' . $pt->id)->getContent();
preg_match("/data-templates='([^']*)'/", $h, $m); $tpl = json_decode(html_entity_decode($m[1] ?? '{}'), true);
$check('three templates on the page, in the language the person wrote in', is_array($tpl) && array_keys($tpl) === ['questions', 'quote', 'decline'] && str_contains($tpl['questions']['body'], 'Olá Ana Silva') && str_contains($tpl['questions']['subject'], 'projeto'), json_encode($tpl));
$check('their name and project type are filled in', str_contains($tpl['questions']['body'], '(website institucional / empresa)') && ! str_contains(json_encode($tpl), ':name') && ! str_contains(json_encode($tpl), ':type'));
$tplEn = json_decode(html_entity_decode((preg_match("/data-templates='([^']*)'/", $send('GET', '/admin/leads/' . $en->id)->getContent(), $m2) ? $m2[1] : '{}')), true);
$check('English leads get English templates', str_contains($tplEn['quote']['body'], 'Hi John') && str_contains($tplEn['quote']['body'], 'web application'));
$check('every language has the same three templates', collect(['en', 'pt', 'es'])->every(fn ($l) => array_keys(trans('lead_replies.templates', [], $l)) === ['questions', 'quote', 'decline']));
$check('the page has the reply form, template picker and the script is loaded', str_contains($h, 'Send reply') && str_contains($h, 'data-template-select') && str_contains($h, 'name="body"') && str_contains(file_get_contents('resources/js/admin.js'), 'admin-lead-reply'));

echo "== sending a reply\n";
$before = $mails()->count();
$r = $send('POST', '/admin/leads/' . $pt->id . '/reply', ['subject' => 'O teu projeto', 'body' => "Olá Ana,\n\nObrigado!\nFrancisco"]);
$sent = $mails()->slice($before)->first();
$check('the email goes to the person, with a copy to you, and replies come back to you', $r->getStatusCode() === 302 && $sent && $sent->getTo()[0]->getAddress() === 'ana@example.com' && $sent->getBcc()[0]->getAddress() === config('portfolio.contact_email') && $sent->getReplyTo()[0]->getAddress() === config('portfolio.contact_email') && $sent->getSubject() === 'O teu projeto');
$check('it is kept on the lead, and a new lead becomes "replied"', LeadReply::where('lead_id', $pt->id)->count() === 1 && Lead::find($pt->id)->status === 'replied' && LeadReply::first()->mail_error === null);
$check('the conversation is shown on the page', str_contains($send('GET', '/admin/leads/' . $pt->id)->getContent(), 'O teu projeto') && str_contains($send('GET', '/admin/leads/' . $pt->id)->getContent(), 'Obrigado!'));
Lead::where('id', $pt->id)->update(['status' => 'won']);
$send('POST', '/admin/leads/' . $pt->id . '/reply', ['subject' => 'Mais uma coisa', 'body' => 'Esqueci-me de dizer.']);
$check('a lead you already marked won/lost keeps its status when you write again', Lead::find($pt->id)->status === 'won' && LeadReply::where('lead_id', $pt->id)->count() === 2);

echo "== safety\n";
$n = $mails()->count(); $replies = LeadReply::count();
$r = $send('POST', '/admin/leads/' . $en->id . '/reply', ['subject' => 'Price', 'body' => "Hi John,\nPrice: [price]\nBest"]);
$check('a template with [brackets] still in it is refused', $mails()->count() === $n && LeadReply::count() === $replies && Lead::find($en->id)->status === 'new');
$send('POST', '/admin/leads/' . $en->id . '/reply', ['subject' => 'See this', 'body' => 'Read [the guide](https://example.com/guide) please.']);
$check('a normal link in square brackets is fine', $mails()->count() === $n + 1);
$send('POST', '/admin/leads/' . $en->id . '/reply', ['subject' => '', 'body' => '']);
$send('POST', '/admin/leads/' . $en->id . '/reply', ['subject' => str_repeat('x', 300), 'body' => 'x']);
$check('empty or oversized messages are refused', $mails()->count() === $n + 1);
$check('only the logged-in admin can send', in_array($send('POST', '/admin/leads/' . $en->id . '/reply', ['subject' => 's', 'body' => 'b'], false)->getStatusCode(), [302, 419]) && $mails()->count() === $n + 1);
$check('a lead that does not exist is a 404', $send('POST', '/admin/leads/9999/reply', ['subject' => 's', 'body' => 'b'])->getStatusCode() === 404);
config(['mail.default' => 'failing', 'mail.mailers.failing' => ['transport' => 'smtp', 'host' => '127.0.0.1', 'port' => 1, 'timeout' => 1]]); app('mail.manager')->forgetMailers();
$fresh = Lead::create(['name' => 'Eva', 'email' => 'eva@example.com', 'project_type' => 'landing', 'description' => 'x', 'locale' => 'es', 'mail_sent' => true]);
$send('POST', '/admin/leads/' . $fresh->id . '/reply', ['subject' => 'Hola', 'body' => 'Hola Eva']);
$check('if the email cannot be sent: the text is kept, flagged, and the status does not change', LeadReply::where('lead_id', $fresh->id)->whereNotNull('mail_error')->count() === 1 && Lead::find($fresh->id)->status === 'new');
config(['mail.default' => 'array']); app('mail.manager')->forgetMailers();
$check('...and the page says "not sent"', str_contains($send('GET', '/admin/leads/' . $fresh->id)->getContent(), 'not sent'));
$check('deleting a lead removes its conversation', (function () use ($send, $fresh) { $send('DELETE', '/admin/leads/' . $fresh->id); return LeadReply::where('lead_id', $fresh->id)->count() === 0; })());

echo "== reminders for unanswered leads\n";
Lead::query()->delete();
$old = Lead::create(['name' => 'Old', 'email' => 'old@example.com', 'project_type' => 'other', 'description' => 'x', 'mail_sent' => true]); \Illuminate\Support\Facades\DB::table('leads')->where('id', $old->id)->update(['created_at' => now()->subHours(60)]);
$recent = Lead::create(['name' => 'Recent', 'email' => 'recent@example.com', 'project_type' => 'other', 'description' => 'x', 'mail_sent' => true]);
$answered = Lead::create(['name' => 'Answered', 'email' => 'a@example.com', 'project_type' => 'other', 'description' => 'x', 'status' => 'replied', 'mail_sent' => true]); \Illuminate\Support\Facades\DB::table('leads')->where('id', $answered->id)->update(['created_at' => now()->subDays(5)]);
$n = $mails()->count();
Artisan::call('leads:remind');
$mail = $mails()->slice($n)->first();
$check('one email about the lead that waited more than 2 days (not the recent or answered ones)', $mails()->count() === $n + 1 && str_contains($mail->getSubject(), '1 lead') && str_contains($mail->getTextBody(), 'Old') && ! str_contains($mail->getTextBody(), 'Recent') && ! str_contains($mail->getTextBody(), 'Answered') && str_contains($mail->getTextBody(), '/admin/leads/' . $old->id));
Artisan::call('leads:remind');
$check('the same lead is not mentioned twice', $mails()->count() === $n + 1 && Lead::find($old->id)->reminded_at !== null);
\Illuminate\Support\Facades\DB::table('leads')->where('id', $recent->id)->update(['created_at' => now()->subHours(49)]);
Artisan::call('leads:remind');
$check('a lead that reaches 2 days later is mentioned then', $mails()->count() === $n + 2);
Artisan::call('leads:remind');
$check('quiet when nothing is waiting', $mails()->count() === $n + 2);
$check('it runs every day at 09:00', collect(app(Illuminate\Console\Scheduling\Schedule::class)->events())->contains(fn ($e) => str_contains($e->command, 'leads:remind') && $e->expression === '0 9 * * *'));

echo "== WhatsApp, phone and email buttons\n";
Project::create(['title' => 'Alpha', 'slug' => 'alpha', 'description' => 'D']);
foreach (['/' => 'Message me on WhatsApp', '/pt' => 'Falar comigo no WhatsApp', '/es' => 'Escríbeme por WhatsApp'] as $path => $label) {
    $html = $send('GET', $path, [], false)->getContent();
    $check("$path: WhatsApp button in the right language, opens a chat with a ready message", str_contains($html, $label) && (bool) preg_match('~href="https://wa\.me/351924490605\?text=[^"]+" target="_blank" rel="noopener noreferrer" data-track="whatsapp"~', $html));
}
$home = $send('GET', '/', [], false)->getContent();
$check('the opening message is written in the visitor\'s language', str_contains($home, rawurlencode('Hi Francisco! I found your website')) && str_contains($send('GET', '/pt', [], false)->getContent(), rawurlencode('Olá Francisco!')));
$check('phone and email links are counted too', str_contains($home, 'data-track="phone"') && str_contains($home, 'data-track="email"'));
$check('project pages offer WhatsApp next to "start a project"', str_contains($send('GET', '/projects/alpha', [], false)->getContent(), 'data-track="whatsapp"'));
$check('the dashboard knows these three actions', isset(App\Services\Stats::EVENTS['whatsapp'], App\Services\Stats::EVENTS['phone'], App\Services\Stats::EVENTS['email']));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
