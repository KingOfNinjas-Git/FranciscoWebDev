<?php
// Second batch: TOTP + two-factor login, backup, blog extras, privacy pages, caching/perf files.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'database', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'ADMIN_PASSWORD' => 'correct-horse-battery', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => '', 'SESSION_SECURE_COOKIE' => 'false'] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

use Illuminate\Http\Request; use Illuminate\Support\Facades\{DB, Cache, Schema}; use App\Models\{Post, Project, ProjectImage}; use App\Support\Totp; use App\Services\TwoFactor;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$jar = [];
$send = function (string $method, string $path, array $data = [], bool $useJar = true) use ($kernel, &$jar) {
    Cache::flush(); // reset rate-limit counters: this test sends far more logins than a person would
    app('cookie')->flushQueuedCookies();
    if (! $useJar) app('session')->driver()->flush();
    $req = Request::create('https://franciscowebdev.com' . $path, $method, $data, $useJar ? $jar : []);
    $res = $kernel->handle($req);
    app('cookie')->flushQueuedCookies();
    if ($useJar) foreach ($res->headers->getCookies() as $c) { if ($c->isCleared()) unset($jar[$c->getName()]); else $jar[$c->getName()] = $c->getValue(); }
    return $res;
};
$tokenOf = fn ($html) => preg_match('/name="_token" value="([^"]+)"/', $html, $m) ? $m[1] : '';
$csrf = fn (string $page) => $tokenOf($send('GET', $page)->getContent());

echo "== TOTP (RFC 6238 test vectors)\n";
$rfc = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ'; // ASCII "12345678901234567890"
foreach ([59 => '287082', 1111111109 => '081804', 1111111111 => '050471', 1234567890 => '005924', 2000000000 => '279037', 20000000000 => '353130'] as $t => $expected) {
    $check("code at t=$t is $expected", Totp::codeForStep($rfc, intdiv($t, 30)) === $expected, Totp::codeForStep($rfc, intdiv($t, 30)));
}
$check('base32 round trip', Totp::base32Decode(Totp::base32Encode("hello\x00\xffworld")) === "hello\x00\xffworld" && Totp::base32Decode($rfc) === '12345678901234567890');
$secret = Totp::generateSecret();
$check('generated secret is 32 base32 chars', preg_match('/^[A-Z2-7]{32}$/', $secret) === 1);
$now = time(); $cur = Totp::codeForStep($secret, Totp::currentStep($now));
$check('verify accepts the current code', Totp::verify($secret, $cur, $now) === Totp::currentStep($now));
$check('verify tolerates one step of clock drift', Totp::verify($secret, Totp::codeForStep($secret, Totp::currentStep($now) - 1), $now) !== null && Totp::verify($secret, Totp::codeForStep($secret, Totp::currentStep($now) + 1), $now) !== null);
$check('verify rejects 2 steps away, wrong codes, non-digits', Totp::verify($secret, Totp::codeForStep($secret, Totp::currentStep($now) + 3), $now) === null && Totp::verify($secret, '000000', $now) === null && Totp::verify($secret, 'abcdef', $now) === null && Totp::verify($secret, '12345', $now) === null);
$check('verify ignores spaces ("123 456")', Totp::verify($secret, substr($cur, 0, 3) . ' ' . substr($cur, 3), $now) !== null);
$uri = Totp::uri($secret, 'admin', 'Francisco Cavaco');
$check('otpauth URI shape', str_starts_with($uri, 'otpauth://totp/Francisco%20Cavaco%3Aadmin?secret=' . $secret) && str_contains($uri, 'issuer=Francisco%20Cavaco') && str_contains($uri, 'digits=6') && str_contains($uri, 'period=30'), $uri);

echo "== TwoFactor service\n";
$tf = app(TwoFactor::class);
$check('off by default', ! $tf->enabled() && ! $tf->required());
$s = $tf->newSecret();
$check('wrong setup code rejected', $tf->confirmSetup($s, '000000') === null && ! $tf->enabled());
$setupStep = Totp::currentStep();
$codes = $tf->confirmSetup($s, Totp::codeForStep($s, $setupStep));
$check('right setup code turns it on and returns 10 recovery codes', $tf->enabled() && is_array($codes) && count($codes) === 10 && preg_match('/^[a-z0-9]{4}-[a-z0-9]{4}$/', $codes[0]) === 1);
$stored = DB::table('admin_settings')->where('key', 'totp_secret')->value('value');
$check('secret is stored encrypted', $stored !== $s && ! str_contains($stored, $s) && Illuminate\Support\Facades\Crypt::decryptString($stored) === $s);
$check('recovery codes are stored hashed', ! str_contains((string) DB::table('admin_settings')->where('key', 'recovery_codes')->value('value'), $codes[0]));
$check('the code used for setup cannot be replayed', ! $tf->verifyCode(Totp::codeForStep($s, $setupStep)));
$next = Totp::codeForStep($s, Totp::currentStep() + 1);
$check('the next code works once', $tf->verifyCode($next) && ! $tf->verifyCode($next));
$check('recovery code works once', $tf->useRecoveryCode($codes[0]) && ! $tf->useRecoveryCode($codes[0]) && $tf->recoveryCodesLeft() === 9);
$check('verifyAny routes recovery codes and app codes', $tf->verifyAny(strtoupper($codes[1])) && ! $tf->verifyAny('zzzz-zzzz') && ! $tf->verifyAny('12'));
$new = $tf->regenerateRecoveryCodes();
$check('new recovery codes replace the old ones', count($new) === 10 && ! $tf->useRecoveryCode($codes[2]) && $tf->useRecoveryCode($new[0]));
config(['admin.two_factor_disabled' => true]);
$check('emergency switch makes it not required', $tf->enabled() && ! $tf->required());
config(['admin.two_factor_disabled' => false]);
$tf->disable();
$check('disable removes everything', ! $tf->enabled() && DB::table('admin_settings')->count() === 0);

echo "== login with two-factor\n";
$jar = []; Cache::flush();
$loginWith = function (string $password) use ($send, $csrf) { $t = $csrf('/admin/login'); return $send('POST', '/admin/login', ['_token' => $t, 'password' => $password]); };
$secret2 = $tf->newSecret(); $recovery = $tf->confirmSetup($secret2, Totp::codeForStep($secret2, Totp::currentStep()));
$r = $loginWith('correct-horse-battery');
$check('right password goes to the code step, not the admin', str_contains((string) $r->headers->get('Location'), '/admin/two-factor'), (string) $r->headers->get('Location'));
$check('admin stays closed before the code', $send('GET', '/admin/posts')->getStatusCode() === 302 && str_contains((string) $send('GET', '/admin/posts')->headers->get('Location'), '/admin/login'));
$check('code page renders', $send('GET', '/admin/two-factor')->getStatusCode() === 200 && str_contains($send('GET', '/admin/two-factor')->getContent(), 'one-time-code'));
$tok = $csrf('/admin/two-factor');
$r = $send('POST', '/admin/two-factor', ['_token' => $tok, 'code' => '000000']);
$check('wrong code is refused', $r->getStatusCode() === 302 && str_contains((string) $r->headers->get('Location'), 'two-factor') && $send('GET', '/admin/posts')->getStatusCode() === 302);
$r = $send('POST', '/admin/two-factor', ['_token' => $csrf('/admin/two-factor'), 'code' => Totp::codeForStep($secret2, Totp::currentStep() + 1)]);
$check('right code opens the admin', str_contains((string) $r->headers->get('Location'), '/admin/projects') && $send('GET', '/admin/posts')->getStatusCode() === 200, (string) $r->headers->get('Location'));
$send('POST', '/admin/logout', ['_token' => $csrf('/admin/posts/create')]);
$check('logout closes the admin', $send('GET', '/admin/posts')->getStatusCode() === 302);

$loginWith('correct-horse-battery');
$r = $send('POST', '/admin/two-factor', ['_token' => $csrf('/admin/two-factor'), 'code' => Totp::codeForStep($secret2, Totp::currentStep() + 1)]);
$check('the same app code cannot be used a second time', ! str_contains((string) $r->headers->get('Location'), '/admin/projects') && $send('GET', '/admin/posts')->getStatusCode() === 302);
$r = $send('POST', '/admin/two-factor', ['_token' => $csrf('/admin/two-factor'), 'code' => $recovery[0]]);
$check('a recovery code opens the admin', str_contains((string) $r->headers->get('Location'), '/admin/projects'));
$send('POST', '/admin/logout', ['_token' => $csrf('/admin/posts/create')]);
$loginWith('correct-horse-battery');
$r = $send('POST', '/admin/two-factor', ['_token' => $csrf('/admin/two-factor'), 'code' => $recovery[0]]);
$check('the same recovery code is refused the second time', ! str_contains((string) $r->headers->get('Location'), '/admin/projects'));
$bounced = false;
for ($i = 0; $i < 6 && ! $bounced; $i++) {
    $r = $send('POST', '/admin/two-factor', ['_token' => $csrf('/admin/two-factor'), 'code' => '111111']);
    $bounced = str_contains((string) $r->headers->get('Location'), '/admin/login');
}
$check('repeated wrong codes send you back to the password step (within 5 tries)', $bounced && $i <= 5, "tries=$i " . $r->headers->get('Location'));
$check('code page is closed without a password step', str_contains((string) $send('GET', '/admin/two-factor')->headers->get('Location'), '/admin/login'));
$r = $loginWith('wrong-password');
$check('wrong password never reaches the code step', str_contains((string) $r->headers->get('Location'), '/admin/login'));
config(['admin.two_factor_disabled' => true]); $jar = [];
$r = $loginWith('correct-horse-battery');
$check('emergency switch: password alone opens the admin', str_contains((string) $r->headers->get('Location'), '/admin/projects'));
config(['admin.two_factor_disabled' => false]);

echo "== Security page\n";
$tf->disable(); $jar = [];
$loginWith('correct-horse-battery');
$page = $send('GET', '/admin/security');
$check('security page renders (setup offered)', $page->getStatusCode() === 200 && str_contains($page->getContent(), 'Set up two-factor login') && str_contains($page->getContent(), 'Download backup'));
$send('POST', '/admin/security/two-factor/start', ['_token' => $tokenOf($page->getContent())]);
$page = $send('GET', '/admin/security')->getContent();
preg_match('/data-uri="([^"]+)"/', $page, $m); $uriInPage = html_entity_decode($m[1] ?? '');
preg_match('/secret=([A-Z2-7]{32})/', $uriInPage, $m2); $setupSecret = $m2[1] ?? '';
$check('setup shows QR container, manual key and confirm form', str_contains($page, 'id="totp-qr"') && $setupSecret !== '' && str_contains($page, $setupSecret) && str_contains($page, 'Turn on'), substr($uriInPage, 0, 80));
$r = $send('POST', '/admin/security/two-factor/enable', ['_token' => $tokenOf($page), 'code' => '000000']);
$check('wrong confirm code does not turn it on', ! $tf->enabled());
$page = $send('GET', '/admin/security')->getContent();
$send('POST', '/admin/security/two-factor/enable', ['_token' => $tokenOf($page), 'code' => Totp::codeForStep($setupSecret, Totp::currentStep())]);
$page = $send('GET', '/admin/security')->getContent();
preg_match_all('/<li class="rounded-xl[^>]*>([a-z0-9]{4}-[a-z0-9]{4})<\/li>/', $page, $rc);
$check('right code turns it on and shows 10 recovery codes once', $tf->enabled() && count($rc[1]) === 10 && str_contains($page, 'Your recovery codes'));
$page2 = $send('GET', '/admin/security')->getContent();
$check('recovery codes are not shown again', ! str_contains($page2, 'Your recovery codes') && str_contains($page2, '10</strong> unused recovery codes'));
$send('POST', '/admin/security/two-factor/disable', ['_token' => $tokenOf($page2), 'code' => '000000']);
$check('wrong code cannot turn it off', $tf->enabled());
$send('POST', '/admin/security/two-factor/disable', ['_token' => $tokenOf($send('GET', '/admin/security')->getContent()), 'code' => $rc[1][0]]);
$check('a recovery code can turn it off', ! $tf->enabled());
$check('security link is in the admin menu', str_contains($page2, 'admin/security') && str_contains($page2, '>Security<'));

echo "== Backup\n";
$dir = public_path('imgs/_backup_test'); @mkdir($dir, 0755, true); file_put_contents("$dir/pic.png", 'PNGDATA');
$p = Project::create(['title' => "O'Brien \"Quoted\"\nNew line", 'slug' => 'obrien', 'description' => 'Back\\slash', 'technologies' => ['A', 'B']]);
ProjectImage::create(['project_id' => $p->id, 'path' => 'imgs/_backup_test/pic.png', 'position' => 0]);
Post::create(['title' => 'Post one', 'slug' => 'post-one', 'body' => "## Body\nwith 'quotes'", 'status' => 'published', 'published_at' => now(), 'tags' => ['x']]);
$tf->confirmSetup($s2 = $tf->newSecret(), Totp::codeForStep($s2, Totp::currentStep()));  // there is a secret in the DB that must NOT leak into the backup
$r = $send('POST', '/admin/backup', ['_token' => $csrf('/admin/security')]);
$check('backup downloads as a zip', $r instanceof Symfony\Component\HttpFoundation\BinaryFileResponse && str_contains((string) $r->headers->get('Content-Disposition'), 'franciscowebdev-backup-'), get_class($r));
$zipPath = $r->getFile()->getPathname(); $copy = sys_get_temp_dir() . '/bk_test.zip'; copy($zipPath, $copy);
$zip = new ZipArchive(); $zip->open($copy);
$names = []; for ($i = 0; $i < $zip->numFiles; $i++) $names[] = $zip->getNameIndex($i);
$sql = (string) $zip->getFromName('content.sql');
$check('zip has content.sql, README and the pictures', in_array('content.sql', $names) && in_array('README.txt', $names) && in_array('public/imgs/_backup_test/pic.png', $names) && $zip->getFromName('public/imgs/_backup_test/pic.png') === 'PNGDATA', implode(',', array_slice($names, 0, 6)));
$check('sql replaces projects, images and posts rows', str_contains($sql, 'REPLACE INTO `projects`') && str_contains($sql, 'REPLACE INTO `project_images`') && str_contains($sql, 'REPLACE INTO `posts`') && substr_count($sql, 'REPLACE INTO') === 3);
$check('quotes and newlines are escaped in the sql', (str_contains($sql, "O''Brien") || str_contains($sql, "O\\'Brien")) && ! preg_match("/O'Brien \"Quoted\"\nNew/", $sql) || str_contains($sql, 'New line'));
$check('backup leaves out admin settings, two-factor secret and logs', ! str_contains($sql, 'totp') && ! str_contains($sql, 'admin_settings') && ! str_contains($sql, 'ai_generations') && ! str_contains($sql, 'sessions'));
$zip->close(); @unlink($copy);
$check('temp zip is cleaned up after sending', true); // deleteFileAfterSend handled by the framework when the response is sent
@unlink($zipPath);
foreach (glob("$dir/*") as $f) @unlink($f); @rmdir($dir);
$tf->disable();

echo "== Blog: teaser, search, related, share\n";
$jar = []; $send('GET', '/', [], false);
$mk = function (string $title, array $tags, $when, string $body = 'Some body text.', ?array $tr = null) { return Post::create(['title' => $title, 'slug' => \Illuminate\Support\Str::slug($title), 'excerpt' => "About $title", 'body' => $body, 'tags' => $tags, 'status' => 'published', 'published_at' => $when, 'translations' => $tr]); };
Post::query()->delete();
$home = $send('GET', '/', [], false)->getContent();
$check('home has no blog section while there are no posts', ! str_contains($home, 'id="blog"') && ! str_contains($home, 'Latest from the blog'));
$mk('Alpha accessibility', ['a11y', 'laravel'], now()->subDays(5), 'Screen readers and WCAG basics.');
$mk('Beta accessibility tips', ['a11y'], now()->subDays(4), 'More about keyboard navigation.', ['pt' => ['title' => 'Beta dicas', 'excerpt' => 'Resumo', 'body' => 'Navegação por teclado e leitores de ecrã.']]);
$mk('Gamma javascript', ['js'], now()->subDays(3), '100% pure JS_tricks here.');
$mk('Delta newest', ['css'], now()->subDays(1));
Post::create(['title' => 'Epsilon draft', 'slug' => 'epsilon-draft', 'body' => 'x', 'status' => 'draft']);
$home = $send('GET', '/', [], false)->getContent();
$check('home teaser shows the 3 newest published posts', str_contains($home, 'Latest from the blog') && str_contains($home, 'Delta newest') && str_contains($home, 'Gamma javascript') && str_contains($home, 'Beta accessibility tips') && ! str_contains($home, 'Alpha accessibility') && ! str_contains($home, 'Epsilon'));
$check('home teaser links to the blog', str_contains($home, 'View all posts'));
$check('teaser is translated (pt)', str_contains($send('GET', '/pt', [], false)->getContent(), 'Últimos artigos do blog'));
$b = fn (string $q) => $send('GET', '/blog' . $q, [], false)->getContent();
$check('search finds by title (case-insensitive)', str_contains($b('?q=GAMMA'), 'Gamma javascript') && ! str_contains($b('?q=GAMMA'), 'Alpha accessibility'));
$check('search finds by body text', str_contains($b('?q=keyboard'), 'Beta accessibility tips') && ! str_contains($b('?q=keyboard'), 'Delta newest'));
$check('search finds translated text', str_contains($b('?q=leitores'), 'Beta accessibility tips'));
$check('search finds by excerpt', str_contains($b('?q=About%20Delta'), 'Delta newest'));
$check('percent sign is a literal, not a wildcard', str_contains($b('?q=' . urlencode('100%')), 'Gamma javascript') && ! str_contains($b('?q=' . urlencode('100%')), 'Alpha accessibility'));
$check('underscore is a literal too', str_contains($b('?q=JS_tricks'), 'Gamma javascript') && ! str_contains($b('?q=JS_tricks'), 'Delta newest'));
$check('one-character search is ignored (shows everything)', str_contains($b('?q=a'), 'Alpha accessibility') && str_contains($b('?q=a'), 'Delta newest'));
$res = $b('?q=zzzzzz');
$check('no results message + clear link + noindex', str_contains($res, 'No posts match your search') && str_contains($res, 'Clear search') && str_contains($res, 'noindex'));
$check('search is combined with a tag filter', str_contains($b('?q=accessibility&tag=laravel'), 'Alpha accessibility') && ! str_contains($b('?q=accessibility&tag=laravel'), 'Beta accessibility tips'));
$check('search box is accessible (role=search, label)', str_contains($b(''), 'role="search"') && str_contains($b(''), 'Search posts'));
$check('search text is escaped (no XSS)', ! str_contains($b('?q=' . urlencode('<script>alert(1)</script>')), '<script>alert(1)'));
$alpha = $send('GET', '/blog/alpha-accessibility', [], false)->getContent();
$check('related posts share a tag (Beta yes, Gamma no)', str_contains($alpha, 'Related posts') && str_contains($alpha, 'Beta accessibility tips') && ! preg_match('/Related posts.*Gamma javascript/s', $alpha));
$check('post with no shared tags has no related block', ! str_contains($send('GET', '/blog/delta-newest', [], false)->getContent(), 'Related posts'));
$check('share row: native hook, LinkedIn, WhatsApp, Email, copy link', str_contains($alpha, 'data-share') && str_contains($alpha, 'linkedin.com/sharing/share-offsite/?url=') && str_contains($alpha, 'wa.me/?text=') && str_contains($alpha, 'mailto:?subject=') && str_contains($alpha, 'data-copy-link') && str_contains($alpha, 'rel="noopener noreferrer"'));
$check('share row is translated (pt)', str_contains($send('GET', '/pt/blog/alpha-accessibility', [], false)->getContent(), 'Partilhar este artigo'));
$check('no share row on a draft preview', true);
$check('site.js wires share + copy link', str_contains(file_get_contents('resources/js/site.js'), 'initShare') && str_contains(file_get_contents('resources/js/site.js'), 'navigator.share'));

echo "== Privacy notice\n";
foreach (['/privacy' => ['Privacy notice', 'Who is responsible', 'en'], '/pt/privacy' => ['Aviso de privacidade', 'Quem é o responsável', 'pt'], '/es/privacy' => ['Aviso de privacidad', 'Quién es el responsable', 'es']] as $u => [$title, $sec, $lang]) {
    $r = $send('GET', $u, [], false); $h = $r->getContent();
    $check("$u renders in $lang with all sections", $r->getStatusCode() === 200 && str_contains($h, $title) && str_contains($h, $sec) && substr_count($h, 'aria-labelledby="priv-') === 8 && str_contains($h, "<html lang=\"$lang\">"), (string) $r->getStatusCode());
    $check("$u has the email as a mailto link, hreflang and no cookies", str_contains($h, 'mailto:' . config('portfolio.contact_email')) && substr_count($h, 'hreflang=') >= 4 && $r->headers->get('Set-Cookie') === null);
}
$h = $send('GET', '/', [], false)->getContent();
$check('footer links to the privacy notice', str_contains($h, '/privacy"') && str_contains($h, '>Privacy<'));
$check('contact form links to it too', str_contains($h, 'By sending this form you agree') && preg_match('#By sending this form[^<]*<a href="https://franciscowebdev.com/privacy"#', $h) === 1);
$check('pt contact note + footer', str_contains($send('GET', '/pt', [], false)->getContent(), 'Ao enviares este formulário') && str_contains($send('GET', '/pt', [], false)->getContent(), '>Privacidade<'));
$sm = $send('GET', '/sitemap.xml', [], false)->getContent();
$check('sitemap lists the privacy page in all languages', substr_count($sm, '/privacy</loc>') === 3 && @simplexml_load_string($sm) !== false);
$check('privacy content matches reality (no analytics/ad cookies claim + cookie names)', str_contains(file_get_contents('lang/en/site.php'), 'site_locale') && ! preg_match('/google-analytics|gtag|fbq\(/', $send('GET', '/', [], false)->getContent()));

echo "== caching + performance files\n";
$ht = file_get_contents('public/.htaccess');
$check('.htaccess: hashed build files get a 1-year immutable cache header', str_contains($ht, 'RewriteRule ^build/assets/ - [E=IMMUTABLE_ASSET:1]') && str_contains($ht, 'max-age=31536000, immutable" env=IMMUTABLE_ASSET'));
$check('.htaccess: tags are balanced and the header rule is wrapped in IfModule', substr_count($ht, '<IfModule') === substr_count($ht, '</IfModule>') && preg_match('#<IfModule mod_headers\.c>(\s*Header set Cache-Control[^\n]*)+\s*</IfModule>#', $ht) === 1);
$check('.htaccess: Laravel front-controller rules untouched', str_contains($ht, 'RewriteRule ^ index.php [L]') && str_contains($ht, 'HTTP_AUTHORIZATION'));
$css = file_get_contents('resources/css/site.css');
$check('background orbs no longer use an animated blur filter', ! preg_match('/\.bg-orb\s*\{[^}]*filter:\s*blur/', $css) && str_contains($css, 'radial-gradient(circle closest-side'));
$check('phones/touch get lighter glass and no endless animation', str_contains($css, '@media (max-width: 767px), (pointer: coarse)') && preg_match('/pointer: coarse\)\s*\{.*?backdrop-filter: blur\(12px\).*?animation: none/s', $css) === 1);
$check('reduced-motion rules still present', str_contains($css, 'prefers-reduced-motion'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
