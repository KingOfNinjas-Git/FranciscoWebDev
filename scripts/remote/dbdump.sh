#!/bin/bash
# Runs ON THE SERVER. Database backup with mysqldump (PHP's passthru() is disabled on this host).
# Credentials come from the site's .env and travel through the environment; nothing secret is printed.
# Usage: bash dbdump.sh /home/user/backups/<timestamp>
set -u -o pipefail
B="$1"
cd ~/domains/franciscowebdev.com/public_html || exit 1

getenv() { grep -E "^$1=" .env | head -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'\$//"; }

export MYSQL_PWD="$(getenv DB_PASSWORD)"
if mysqldump --single-transaction --skip-lock-tables --default-character-set=utf8mb4 \
        -h "$(getenv DB_HOST)" -P "$(getenv DB_PORT)" -u "$(getenv DB_USERNAME)" "$(getenv DB_DATABASE)" | gzip > "$B/database.sql.gz"; then
    echo "mysqldump finished"
else
    echo "mysqldump FAILED"; exit 1
fi
unset MYSQL_PWD

gzip -t "$B/database.sql.gz" && echo "database backup is a valid gzip: $(du -h "$B/database.sql.gz" | cut -f1)"
echo "tables in dump: $(zcat "$B/database.sql.gz" | grep -c '^CREATE TABLE')"
