#!/usr/bin/env bash
# One-command deploy to the Hostinger server, with a backup first and checks before and after.
#
#   scripts/deploy.sh              asks before changing anything on the server
#   scripts/deploy.sh --yes        no questions (for when you are sure)
#   scripts/deploy.sh --skip-tests skip the local test run (the GitHub check still runs on every push)
#
# What it does: checks that the code on your PC is committed, pushed and tested; builds the CSS/JS; backs up the files
# it will replace, the old build and the database on the server; uploads the new build; copies only the changed files
# out of GitHub's main branch; runs new database migrations; clears caches; then loads the main pages to prove they work.
# It never touches .env and never deletes anything on the server except the previous build.
#
# Needs: git, node/npm, php (to run the tests), ssh with the key below. Run it from Git Bash.

set -euo pipefail
cd "$(dirname "$0")/.."

SSH_KEY="${DEPLOY_SSH_KEY:-$HOME/.ssh/hostinger_franciscowebdev}"
SSH_TARGET="${DEPLOY_SSH_TARGET:-u821455701@141.136.33.156}"
SSH_PORT="${DEPLOY_SSH_PORT:-65002}"
SITE_DIR='~/domains/franciscowebdev.com/public_html'
SITE_URL="${DEPLOY_SITE_URL:-https://franciscowebdev.com}"

YES=0; RUN_TESTS=1
for arg in "$@"; do
    case "$arg" in
        --yes) YES=1 ;;
        --skip-tests) RUN_TESTS=0 ;;
        *) echo "Unknown option: $arg"; exit 2 ;;
    esac
done

say()  { printf '\n\033[1m== %s\033[0m\n' "$*"; }
ok()   { printf '  \033[32mok\033[0m   %s\n' "$*"; }
bad()  { printf '  \033[31mFAIL\033[0m %s\n' "$*"; }
die()  { printf '\n\033[31mStopped: %s\033[0m\n' "$*"; exit 1; }
remote() { ssh -i "$SSH_KEY" -p "$SSH_PORT" -o BatchMode=yes -o LogLevel=ERROR "$SSH_TARGET" "$@"; }
php_bin() { if command -v php >/dev/null 2>&1; then php "$@"; else "$HOME/.config/herd/bin/php.bat" "$@"; fi; }

say "1. Checking the code on this computer"
[ -z "$(git status --porcelain --untracked-files=no)" ] || die "there are uncommitted changes. Commit them first."
git fetch -q origin main
[ "$(git rev-parse HEAD)" = "$(git rev-parse origin/main)" ] || die "this computer and GitHub's main branch differ. Push (or pull) first so GitHub has exactly what you are deploying."
ok "committed and pushed: $(git log --oneline -1)"

if [ "$RUN_TESTS" = 1 ]; then
    php_bin vendor/bin/pest >/tmp/deploy-tests.log 2>&1 && ok "tests pass" || { tail -25 /tmp/deploy-tests.log; die "tests fail. Fix them (or use --skip-tests if you really must)."; }
fi

npm run build >/tmp/deploy-build.log 2>&1 && ok "assets built" || { tail -15 /tmp/deploy-build.log; die "the asset build failed."; }

say "2. Connecting to the server"
remote "cd $SITE_DIR && git fetch -q origin main && echo connected" >/dev/null || die "cannot reach the server over SSH."
[ "$(remote "cd $SITE_DIR && git rev-parse origin/main")" = "$(git rev-parse HEAD)" ] || die "the server did not receive the latest commit from GitHub."
ok "server can see this commit"

say "3. Backing up on the server"
remote 'bash -s' < scripts/remote/backup.sh | tee /tmp/deploy-backup.log
BACKUP_DIR="$(grep -o 'BACKUP_DIR=.*' /tmp/deploy-backup.log | tail -1 | cut -d= -f2)"
[ -n "$BACKUP_DIR" ] || die "the backup did not report its folder."
remote "bash -s -- $BACKUP_DIR" < scripts/remote/dbdump.sh | grep -v '^$'
remote "test -s $BACKUP_DIR/database.sql.gz" || die "the database backup is empty."
ok "backup saved in $BACKUP_DIR"

say "4. What this deploy will change"
FILES="$(remote "cat $BACKUP_DIR/deploy-files.txt")"
echo "$FILES" | sed 's/^/   /'
if echo "$FILES" | grep -qx 'composer.lock'; then
    die "composer.lock changed, so PHP packages need updating on the server first (composer install --no-dev -o). Do that by hand, then re-run."
fi
PENDING="$(remote "cd $SITE_DIR && php artisan migrate:status --pending 2>/dev/null | grep -c 'Pending' || true")"
echo "   database migrations waiting: ${PENDING:-0}"
if [ "$YES" = 0 ]; then
    read -r -p "Deploy this to $SITE_URL ? [y/N] " answer
    [ "$answer" = "y" ] || [ "$answer" = "Y" ] || die "cancelled. Nothing was changed (the backup is harmless)."
fi

say "5. Uploading"
tar -C public -cz build | remote "cd $SITE_DIR/public && rm -rf .build-new && mkdir .build-new && tar -xz -C .build-new"
remote "cd $SITE_DIR && git archive origin/main \$(cat $BACKUP_DIR/deploy-files.txt) | tar -x -C . && cd public && mv build .build-old && mv .build-new/build build && rmdir .build-new && rm -rf .build-old"
ok "build and files in place"
if [ "${PENDING:-0}" != "0" ]; then
    remote "cd $SITE_DIR && php artisan migrate --force" | sed 's/^/   /'
    ok "migrations ran"
fi
remote "cd $SITE_DIR && php artisan optimize:clear" >/dev/null
ok "caches cleared"

say "6. Checking the result"
DIFFS=0
for f in $FILES; do
    remote "cd $SITE_DIR && git show origin/main:$f | diff -q --strip-trailing-cr - $f" >/dev/null 2>&1 || { bad "$f differs from GitHub"; DIFFS=1; }
done
[ "$DIFFS" = 0 ] && ok "every deployed file matches GitHub"

FAILS=0
for path in / /pt /es /blog /sitemap.xml /health; do
    code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "$SITE_URL$path")"
    if [ "$code" = 200 ]; then ok "$path -> 200"; else bad "$path -> $code"; FAILS=1; fi
done
code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "$SITE_URL/definitely-not-a-page")"
[ "$code" = 404 ] && ok "unknown page -> 404" || { bad "unknown page -> $code"; FAILS=1; }

NEW_ERRORS="$(remote "cd $SITE_DIR && grep -c \"^\\[$(date -u +%Y-%m-%d) .*production.ERROR\" storage/logs/laravel.log 2>/dev/null || true")"
echo "   error lines in today's server log: ${NEW_ERRORS:-0} (compare with before if this looks high)"

if [ "$FAILS" = 0 ] && [ "$DIFFS" = 0 ]; then
    printf '\n\033[32mDeployed.\033[0m To undo: the old files and build are in %s on the server.\n' "$BACKUP_DIR"
else
    printf '\n\033[31mDeployed, but some checks failed.\033[0m Backup for rolling back: %s (see docs/OPERATIONS.md).\n' "$BACKUP_DIR"
    exit 1
fi
