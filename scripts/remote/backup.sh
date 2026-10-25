#!/bin/bash
# Runs ON THE SERVER (sent over ssh by scripts/deploy.sh). Backs up everything the deploy is about to replace.
# Prints the backup folder on the last line as BACKUP_DIR=...
set -u
SITE=~/domains/franciscowebdev.com/public_html
TS=$(date +%Y%m%d-%H%M%S)
B=~/backups/$TS
mkdir -p "$B" && chmod 700 ~/backups "$B"
cd "$SITE" || exit 1

# The files a deploy would replace: committed files whose content differs (ignoring line endings) or that are missing.
# Pictures under public/imgs are skipped on purpose (uploads and the optimiser change them on the server), and so are
# files that only matter on a developer's machine.
git ls-tree -r origin/main | while read -r mode type sha path; do
    case "$path" in public/imgs/*|Dockerfile|package.json|package-lock.json|README.md|.github/*|tests/*|scripts/*|docs/*) continue;; esac
    if [ -f "$path" ]; then
        git show "origin/main:$path" | diff -q --strip-trailing-cr - "$path" >/dev/null 2>&1 || echo "$path"
    else
        echo "$path"
    fi
done > "$B/deploy-files.txt"

echo "files to replace or add: $(wc -l < "$B/deploy-files.txt")"
echo "  of which new on the server: $(while read -r f; do [ -f "$f" ] || echo x; done < "$B/deploy-files.txt" | wc -l)"

# current versions of those files, and the old build, so they can be put back
while read -r f; do [ -f "$f" ] && echo "$f"; done < "$B/deploy-files.txt" | tar czf "$B/code-before.tgz" -T -
tar czf "$B/build-before.tgz" public/build 2>/dev/null
echo "code backup: $(du -h "$B/code-before.tgz" | cut -f1) | build backup: $(du -h "$B/build-before.tgz" | cut -f1)"

cp -p "$SITE/.env" "$B/env.backup" && chmod 600 "$B/env.backup"   # a private copy, never printed
echo "BACKUP_DIR=$B"
