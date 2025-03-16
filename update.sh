#!/bin/bash

# دایرکتوری کاری
cd /var/www/panel || {
    echo "Failed to change directory" >> /var/www/panel/git_pull_log.txt
    exit 1
}

# لاگ شروع
echo "$(date '+%Y-%m-%d %H:%M:%S') - Starting update process" >> /var/www/panel/git_pull_log.txt

# فچ کردن تغییرات
git fetch origin >> /var/www/panel/git_pull_log.txt 2>&1

# گرفتن وضعیت فعلی
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [ "$CURRENT_BRANCH" != "main" ]; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Switching to main branch" >> /var/www/panel/git_pull_log.txt
    git checkout main >> /var/www/panel/git_pull_log.txt 2>&1
fi

# بررسی وجود تغییرات محلی
if git status --porcelain | grep -q .; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Local changes detected, resetting..." >> /var/www/panel/git_pull_log.txt
    git reset --hard origin/main >> /var/www/panel/git_pull_log.txt 2>&1
else
    echo "$(date '+%Y-%m-%d %H:%M:%S') - No local changes, pulling..." >> /var/www/panel/git_pull_log.txt
    git pull origin main >> /var/www/panel/git_pull_log.txt 2>&1 &
fi

# ثبت پایان
echo "$(date '+%Y-%m-%d %H:%M:%S') - Update process completed" >> /var/www/panel/git_pull_log.txt