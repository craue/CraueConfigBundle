#!/bin/sh

wget http://getcomposer.org/composer.phar

php composer.phar config -g preferred-install source

if [ -n "${MIN_STABILITY:-}" ]; then
	sed -i -e "s/\"minimum-stability\": \"stable\"/\"minimum-stability\": \"${MIN_STABILITY}\"/" composer.json
fi

php composer.phar --no-interaction require --no-update symfony/framework-bundle:${SYMFONY_VERSION}
php composer.phar --no-interaction require --no-update --dev symfony/symfony:${SYMFONY_VERSION}
php composer.phar --no-interaction update
