#!/bin/sh

composer config -g preferred-install source

if [ -n "${MIN_STABILITY:-}" ]; then
	sed -i -e "s/\"minimum-stability\": \"stable\"/\"minimum-stability\": \"${MIN_STABILITY}\"/" composer.json
fi

composer --no-interaction require --no-update symfony/framework-bundle:${SYMFONY_VERSION}
composer --no-interaction require --no-update --dev symfony/symfony:${SYMFONY_VERSION}
composer --no-interaction update
