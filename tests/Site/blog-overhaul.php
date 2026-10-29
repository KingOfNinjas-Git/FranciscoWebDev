<?php
// Blog overhaul: featured post, topic chips, linkable headings, table of contents, author box, translations.
chdir($argv[1]);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://franciscowebdev.com', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'AI_PROVIDER' => 'anthropic', 'ANTHROPIC_API_KEY' => 'x', 'GEMINI_API_KEY' => ''] as $k => $v) {
    putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
}
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);


use Illuminate\Http\Request; use App\Models\Post;
$pass = 0; $fail = 0;
$check = function (string $name, bool $ok, string $extra = '') use (&$pass, &$fail) { echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($extra && !$ok ? "  -> " . substr($extra, 0, 300) : '') . "\n"; $ok ? $pass++ : $fail++; };
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$get = fn (string $path) => $kernel->handle(Request::create('https://franciscowebdev.com' . $path))->getContent();

$long = "Intro paragraph.\n\n## Quote basics\n\nText.\n\n### Extras\n\nMore.\n\n## Quote basics\n\nSame heading twice.\n\n## Next steps\n\n```html\n<p>hi</p>\n```\n\n## Ünïcode & <b>tags</b>\n\nEnd.";
$mk = fn ($title, $slug, $body, $tags, $days, $extra = []) => Post::create(array_merge(['title' => $title, 'slug' => $slug, 'excerpt' => "About $title", 'body' => $body, 'tags' => $tags, 'status' => 'published', 'published_at' => now()->subDays($days)], $extra));
$mk('Newest post', 'newest', $long, ['alpha', 'beta'], 1, ['translations' => ['pt' => ['title' => 'Post mais recente', 'body' => "## Primeiro\n\nTexto.\n\n## Segundo\n\nTexto.\n\n## Terceiro\n\nTexto."]]]);
$mk('Second post', 'second', "Short body without headings.", ['alpha'], 2);
$mk('Third post', 'third', "## One\n\na\n\n## Two\n\nb", ['gamma'], 3);
foreach (range(1, 8) as $i) { $mk("Topic filler $i", "filler-$i", 'x', ["tag-$i"], 10 + $i); }

echo "== headings you can link to\n";
$a = Post::where('slug', 'newest')->first()->article();
$ids = array_column($a['toc'], 'id');
$check('every h2/h3 is in the list, in order', array_column($a['toc'], 'text') === ['Quote basics', 'Extras', 'Quote basics', 'Next steps', 'Ünïcode & <b>tags</b>'] || array_column($a['toc'], 'text') === ['Quote basics', 'Extras', 'Quote basics', 'Next steps', 'Ünïcode & tags'], json_encode(array_column($a['toc'], 'text')));
$check('levels are kept (h3 is a sub-item)', array_column($a['toc'], 'level') === [2, 3, 2, 2, 2]);
$check('the same heading twice gets two different ids', $ids[0] === 'quote-basics' && $ids[2] === 'quote-basics-2' && count(array_unique($ids)) === count($ids), json_encode($ids));
$check('each heading carries its id and a "#" link with a readable label', str_contains($a['html'], '<h2 id="quote-basics">Quote basics<a class="heading-anchor" href="#quote-basics" aria-label="Link to the section &quot;Quote basics&quot;">#</a></h2>'), substr($a['html'], 0, 400));
$check('raw tags in a heading stay stripped (no injected markup)', ! str_contains($a['html'], '<b>'));
$check('code blocks are untouched', str_contains($a['html'], '<pre><code'));

echo "== post page\n";
$h = $get('/blog/newest');
$check('table of contents shown for a post with 3+ headings', str_contains($h, 'data-toc') && str_contains($h, 'On this page') && str_contains($h, 'href="#quote-basics"') && str_contains($h, 'toc-sub'));
$check('reading progress bar, author line and author box', str_contains($h, 'id="read-progress-bar"') && str_contains($h, 'Francisco Cavaco') && str_contains($h, 'author-box') && str_contains($h, 'Written by Francisco Cavaco') && str_contains($h, '#contact'));
$check('labels for the copy-code buttons are handed to the script', str_contains($h, 'data-l-copy="Copy"') && str_contains($h, 'data-l-copied="Copied!"'));
$check('published date is a machine-readable <time>', (bool) preg_match('/<time datetime="\d{4}-\d{2}-\d{2}">/', $h));
$h2 = $get('/blog/second');
$check('no table of contents when there are fewer than 3 headings', ! str_contains($h2, 'data-toc') && ! str_contains($h2, 'On this page') && str_contains($h2, 'class="blog-layout "'), '');
$check('JSON-LD and breadcrumbs are still there', str_contains($h, '"@type":"BlogPosting"') && str_contains($h, '"@type":"BreadcrumbList"'));
$draft = $mk('A draft', 'a-draft', "## a\n\n## b\n\n## c", ['x'], 0, ['status' => 'draft', 'published_at' => null]);
$check('visitors cannot open a draft', $kernel->handle(Request::create('https://franciscowebdev.com/blog/a-draft'))->getStatusCode() === 404);

echo "== languages\n";
$pt = $get('/pt/blog/newest');
$check('Portuguese post: translated title, contents, author box, labels', str_contains($pt, 'Post mais recente') && str_contains($pt, 'Nesta página') && str_contains($pt, 'href="#primeiro"') && str_contains($pt, 'Escrito por Francisco Cavaco') && str_contains($pt, 'data-l-copy="Copiar"') && str_contains($pt, 'aria-label="Ligação para a secção &quot;Primeiro&quot;"'));
$es = $get('/es/blog/newest');
$check('Spanish post: UI in Spanish, English text falls back', str_contains($es, 'En esta página') && str_contains($es, 'Escrito por Francisco Cavaco') && str_contains($es, 'Newest post') && str_contains($es, 'href="#quote-basics"'));

echo "== blog list\n";
$l = $get('/blog');
$check('the newest post is the big featured card, once', str_contains($l, 'blog-featured') && substr_count($l, 'Newest post') >= 1 && substr_count($l, 'class="reveal glass glass-hover blog-featured') === 1 && str_contains($l, 'Latest post'));
$check('the other posts are normal cards, the featured one is not repeated', substr_count($l, 'blog-card ') >= 2 && substr_count($l, '>Newest post</h2>') === 1);
$check('count line: articles and topics', str_contains($l, '11 articles') && (bool) preg_match('/\d+ topics/', $l), '');
$check('topic chips show how many posts use each tag', str_contains($l, 'alpha<span class="tag-count">2</span>'));
$check('extra topics sit behind "More topics"', str_contains($l, 'topics-more') && (bool) preg_match('/More topics \(\+\d+\)/', $l) && str_contains($l, 'Fewer topics'));
$check('RSS link in the header', str_contains($l, 'RSS feed') && str_contains($l, '/blog/feed.xml'));
$f = $get('/blog?tag=gamma');
$check('a tag filter has no featured card, just the posts', ! str_contains($f, 'blog-featured') && str_contains($f, 'Third post') && ! str_contains($f, 'Newest post</h2>'));
$s = $get('/blog?q=Second');
$check('search has no featured card and keeps working', ! str_contains($s, 'blog-featured') && str_contains($s, 'Second post') && str_contains($s, 'Results for'));
$tagLast = $get('/blog?tag=tag-8');
$check('an active tag hidden under "More topics" opens that group', (bool) preg_match('/topics-more mt-3" +open/', $tagLast), '');
$p2 = $get('/blog?page=2');
$check('page 2 has no featured card', ! str_contains($p2, 'blog-featured'));
$ptl = $get('/pt/blog');
$check('Portuguese list: counts, featured label and more-topics text translated', str_contains($ptl, 'artigos') && str_contains($ptl, 'Artigo mais recente') && str_contains($ptl, 'Mais temas'));

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
