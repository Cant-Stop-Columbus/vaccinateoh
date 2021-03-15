#/bin/bash
declare -p | grep -Ev 'BASHOPTS|BASH_VERSINFO|EUID|PPID|SHELLOPTS|UID' > /tmp/cron-env
chmod +x /tmp/cron-env