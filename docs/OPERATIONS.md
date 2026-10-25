# Running the site

A short guide to the things you do (or need) when the site is live. Everything here is for franciscowebdev.com on Hostinger.

## Deploying a change

1. Commit and push your work to GitHub (`main`).
2. In Git Bash, from the project folder:

   ```bash
   scripts/deploy.sh
   ```

   It runs the tests, builds the CSS/JS, backs up the server, shows you what will change, asks for a "y", uploads, runs new database migrations, clears caches and checks the main pages. Use `--yes` to skip the question and `--skip-tests` to skip the local test run.

It never touches `.env` and only deletes the previous build. If anything looks wrong afterwards, see "Undoing a deploy".

## Undoing a deploy

Every deploy leaves a folder on the server: `~/backups/<date-time>/` containing

| File | What it is |
|---|---|
| `code-before.tgz` | the old version of every file the deploy replaced |
| `build-before.tgz` | the old built CSS/JS |
| `database.sql.gz` | the whole database before the deploy |
| `env.backup` | a private copy of `.env` |
| `deploy-files.txt` | the list of files that were replaced or added |

To go back (connect with `ssh -i ~/.ssh/hostinger_franciscowebdev -p 65002 u821455701@141.136.33.156`):

```bash
cd ~/domains/franciscowebdev.com/public_html
B=~/backups/<the folder>
tar xzf $B/code-before.tgz              # old files
rm -rf public/build && tar xzf $B/build-before.tgz   # old CSS/JS
php artisan optimize:clear
```

Files that were *added* by the deploy stay (they are harmless without the files that use them). Only restore the database (`zcat $B/database.sql.gz | mysql ...`) if the deploy changed the database and you need that undone; ask for help before doing it, because it overwrites everything written since the backup (new leads, new posts).

## Knowing when something breaks

The site watches itself in three ways:

- **Error emails.** If a visitor gets a server error (a 500 page), you get an email with the page and the error. At most one every 10 minutes, and it never contains form data.
- **Self-check every 15 minutes** (`php artisan site:check`). Loads the home pages, the blog and the sitemap and emails you if one is broken, and when the HTTPS certificate has less than 14 days left. At most one email per hour. It only runs if the server's cron is set up (below).
- **`/health`.** Returns `{"ok":true}` when the site and its database work, or HTTP 503 when not. Point an outside monitor at it (below).

### Turn on the self-check (once, in hPanel)

hPanel -> Advanced -> **Cron Jobs** -> add a job that runs every minute:

```
/usr/bin/php /home/u821455701/domains/franciscowebdev.com/public_html/artisan schedule:run >> /dev/null 2>&1
```

(If hPanel offers a "PHP" job type, use it with the path `domains/franciscowebdev.com/public_html/artisan schedule:run`.)

### Outside uptime monitor (free)

The self-check lives on the same server, so it cannot warn you when the whole server is down. A free outside monitor can:

1. Sign up at uptimerobot.com (free plan).
2. Add a monitor: type **HTTPS**, URL `https://franciscowebdev.com/health`, interval 5 minutes, alert to your email.

## Tests

`vendor/bin/pest` runs every check in `tests/Site` (about 30 seconds): pages in three languages, the contact form and its emails, the admin, blog and AI drafting, images, translations, security headers and more. GitHub runs them on every push; look for the green tick next to a commit.

## Where things live on the server

- Project and website: `~/domains/franciscowebdev.com/public_html` (the web root is its `public/` folder, via the root `.htaccess`).
- Logs: `storage/logs/laravel.log`.
- Backups: `~/backups/`.
- Settings and secrets: `.env` (never committed, never shown, never in a backup you share).
- The server's own git checkout is old on purpose; do not `git pull` or `git reset` there. The deploy script copies only the changed files out of GitHub.
