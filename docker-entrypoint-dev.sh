#!/bin/bash
set -e

#
# If we're starting web-server we need to do following:
#   1) Modify docker-php-ext-xdebug.ini file to contain correct remote host value, note that for mac we need to use
#      another value within this. Also we want to export host IP so that we can use that within `check.php` to check
#      that current environment is compatible with Symfony.
#   2) Install all dependencies
#   3) Generate JWT encryption keys + allow apache to read this file
#   4) Create database if it not exists yet
#   5) Run possible migrations, so that database is always up to date
#   6) Ensure that _all_ files have "correct" permissions
#
# Note that all the chmod stuff is for users who are using docker-compose within Linux environment. More info in link
# below:
#   https://jtreminio.com/blog/running-docker-containers-as-current-host-user/
#

# Step 1
if [[ -z "${DOCKER_WITH_MAC}" ]]; then
  # Not Mac, so determine actual docker container IP address
  HOST=`/sbin/ip route|awk '/default/ { print $3 }'`
else
  # Otherwise use special value, which works wit Mac
  HOST="docker.for.mac.localhost"
fi

sed -i "s/xdebug\.remote_host \=.*/xdebug\.remote_host\=$HOST/g" /usr/local/etc/php/php.ini

export DOCKER_IP=`/sbin/ip route|awk '/default/ { print $3 }'`

# Step 2
COMPOSER_MEMORY_LIMIT=-1 composer install

# Step 3
make generate-jwt-keys
chmod 644 /app/config/jwt/private.pem

# Step 4
./bin/console doctrine:database:create --no-interaction --if-not-exists

# Step 5
./bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --all-or-nothing

# Step 6
chmod -R o+s+w /app

exec "$@"
