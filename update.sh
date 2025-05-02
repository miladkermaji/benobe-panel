#!/bin/bash
# قبل از دستورات git این خط را اضافه کنید
export GIT_CONFIG_NOSYSTEM=1
export XDG_CONFIG_HOME=/tmp
# دایرکتوری کاری
WORKING_DIR="/var/www/panel"
LOG_FILE="$WORKING_DIR/git_pull_log.txt"

# بررسی وجود دایرکتوری
if ! cd "$WORKING_DIR"; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Failed to change to $WORKING_DIR" >> "$LOG_FILE"
    exit 1
fi

# لاگ شروع
echo "$(date '+%Y-%m-%d %H:%M:%S') - Starting update process" >> "$LOG_FILE"

# اطمینان از دسترسی‌های گیت
git config --global --add safe.directory "$WORKING_DIR" >> "$LOG_FILE" 2>&1

# فچ کردن تغییرات
if ! git fetch origin >> "$LOG_FILE" 2>&1; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Git fetch failed" >> "$LOG_FILE"
    exit 1
fi

# گرفتن شاخه فعلی
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [ "$CURRENT_BRANCH" != "main" ]; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Switching to main branch" >> "$LOG_FILE"
    if ! git checkout main >> "$LOG_FILE" 2>&1; then
        echo "$(date '+%Y-%m-%d %H:%M:%S') - Failed to switch to main" >> "$LOG_FILE"
        exit 1
    fi
fi

# بررسی تغییرات محلی و ریست سخت در صورت نیاز
if git status --porcelain | grep -q .; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Local changes detected, resetting..." >> "$LOG_FILE"
    if ! git reset --hard origin/main >> "$LOG_FILE" 2>&1; then
        echo "$(date '+%Y-%m-%d %H:%M:%S') - Reset failed" >> "$LOG_FILE"
        exit 1
    fi
fi

# کشیدن تغییرات
echo "$(date '+%Y-%m-%d %H:%M:%S') - Pulling changes..." >> "$LOG_FILE"
if ! git pull origin main >> "$LOG_FILE" 2>&1; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Git pull failed" >> "$LOG_FILE"
    exit 1
fi

# ثبت پایان
echo "$(date '+%Y-%m-%d %H:%M:%S') - Update process completed successfully" >> "$LOG_FILE"
exit 0