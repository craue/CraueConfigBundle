#!/bin/sh

composer self-update
composer config -g preferred-install source

if [ -n "${MIN_STABILITY:-}" ]; then
	sed -i -e "s/\"minimum-stability\": \"stable\"/\"minimum-stability\": \"${MIN_STABILITY}\"/" composer.json
fi

composer --no-interaction remove --no-update symfony/framework-bundle
composer --no-interaction require --no-update --dev symfony/symfony:${SYMFONY_VERSION}
composer --no-interaction update
