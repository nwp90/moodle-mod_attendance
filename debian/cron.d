#
# Cron job for the 'otagounifom' Moodle instance
#
# -t 1440 is max lockfile age of 1 day before ignoring
# -d 0    is delay 0s after top of minute before starting job (to allow for multiple machines' crons all firing at once)
# -f arg is lockfile to use
#
4-59/5 * * * * www-data /usr/bin/aexec -t 1440 -f /run/lock/moodle-cron.lock -d 0 /usr/bin/php /srv/moodle/otagounifom/moodle/admin/cli/cron.php >> /var/log/sitelogs/moodle-site-otagounifom/cron.log 2>&1
#
# Old-style
#
# moodle-site-otagounifom is SITENAME arg to crondispatcher (used to construct log path)
# 11 is DELAY arg to crondispatcher (used as -d above)
#4-59/5 * * * * www-data /usr/bin/site-crondispatcher /srv/moodle/otagounifom/moodle/admin/cli/cron.php moodle-site-otagounifom 11
