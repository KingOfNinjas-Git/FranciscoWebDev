#!/usr/bin/env bash
# Copies the site's safety copies from the Hostinger server to THIS computer, so they also exist somewhere else.
#
#   scripts/pull-backups.sh                 copy to ~/Backups/franciscowebdev
#   scripts/pull-backups.sh /d/MyBackups    copy somewhere else (a drive that is synced to a cloud is perfect)
#
# What it copies (read-only on the server; nothing there is changed or deleted):
#   - every nightly database dump (storage/app/backups/database-*.sql.gz)
#   - the pictures and videos you upload in the admin (public/imgs and public/videos), only what is new since last time
# It never copies .env (passwords). The Monday email tells you how long ago this last ran: once a week is plenty.
#
# After a successful copy it leaves a small note on the server (storage/app/backups/.last-offsite-pull) so the weekly
# report can say "copied to your computer 2 days ago".
#
# Needs: ssh and tar (both come with Git Bash) and the key below. Run it from Git Bash.

set -euo pipefail

SSH_KEY="${DEPLOY_SSH_KEY:-$HOME/.ssh/hostinger_franciscowebdev}"
SSH_TARGET="${DEPLOY_SSH_TARGET:-u821455701@141.136.33.156}"
SSH_PORT="${DEPLOY_SSH_PORT:-65002}"
SITE_DIR='~/domains/franciscowebdev.com/public_html'
DEST="${1:-$HOME/Backups/franciscowebdev}"
KEEP_DUMPS=30

say()  { printf '\n\033[1m== %s\033[0m\n' "$*"; }
ok()   { printf '  \033[32mok\033[0m   %s\n' "$*"; }
die()  { printf '\n\033[31mStopped: %s\033[0m\n' "$*"; exit 1; }
remote() { ssh -i "$SSH_KEY" -p "$SSH_PORT" -o BatchMode=yes -o LogLevel=ERROR "$SSH_TARGET" "$@"; }

say "1. Connecting to the server"
remote "echo connected" >/dev/null 2>&1 || die "could not connect over ssh (is the key $SSH_KEY there, and is the internet on?)"
ok "connected"

mkdir -p "$DEST/database" "$DEST/files"

say "2. Database dumps"
new=0
for name in $(remote "ls $SITE_DIR/storage/app/backups/ 2>/dev/null | grep '^database-.*\.sql\.gz$'"); do
    if [ ! -s "$DEST/database/$name" ]; then
        remote "cat $SITE_DIR/storage/app/backups/$name" > "$DEST/database/$name.part"
        # a dump is a gzip file: only keep it if it really is one
        if gzip -t "$DEST/database/$name.part" 2>/dev/null; then
            mv "$DEST/database/$name.part" "$DEST/database/$name"; new=$((new + 1))
        else
            rm -f "$DEST/database/$name.part"; die "$name did not arrive intact"
        fi
    fi
done
# keep the newest $KEEP_DUMPS here
ls -1 "$DEST/database"/database-*.sql.gz 2>/dev/null | sort -r | tail -n +$((KEEP_DUMPS + 1)) | while read -r old; do rm -f "$old"; done
total=$(ls -1 "$DEST/database"/database-*.sql.gz 2>/dev/null | wc -l | tr -d ' ')
[ "$total" -gt 0 ] || die "there are no database dumps on the server yet"
ok "$new new dump(s), $total kept here (newest: $(ls -1 "$DEST/database" | sort | tail -1))"

say "3. Pictures and videos"
stamp_file="$DEST/.last-files-pull"
since=0; [ -f "$stamp_file" ] && since=$(cat "$stamp_file")
now=$(remote "date +%s")
# on the very first run this is everything; afterwards only files changed since the last run
if remote "cd $SITE_DIR/public && tar cz --newer-mtime=@$since imgs videos 2>/dev/null" | tar xz -C "$DEST/files" 2>/dev/null; then
    ok "files up to date ($(find "$DEST/files" -type f | wc -l | tr -d ' ') files, $(du -sh "$DEST/files" | cut -f1) in $DEST/files)"
else
    die "could not copy the pictures and videos"
fi
echo "$now" > "$stamp_file"

say "4. Telling the server the copy is done"
remote "mkdir -p $SITE_DIR/storage/app/backups && echo $now > $SITE_DIR/storage/app/backups/.last-offsite-pull"
ok "done. Everything is in: $DEST"
