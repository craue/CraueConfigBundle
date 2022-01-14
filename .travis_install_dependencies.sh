#!/bin/bash

set -euv

export COMPOSER_NO_INTERACTION=1
composer self-update

# install Symfony Flex
composer require --no-progress --no-scripts --no-plugins symfony/flex

case "${DEPS:-}" in
	'lowest')
		COMPOSER_UPDATE_ARGS='--prefer-lowest'
		;;
	'unmodified')
		# don't modify dependencies, install them as defined
		;;
	*)
		if [ -n "${MIN_STABILITY:-}" ]; then
			composer config minimum-stability "${MIN_STABILITY}"
		fi

		if [ -n "${SYMFONY_VERSION:-}" ]; then
			composer config extra.symfony.require "${SYMFONY_VERSION}"
		fi
esac

if [ -n "${WITH_STATIC_ANALYSIS:-}" ]; then
	composer require --no-update --dev "phpstan/phpstan:^0.12"
fi

if [ -n "${WITH_DOCTRINE_CACHE_BUNDLE:-}" ]; then
	composer require --no-update --dev "doctrine/doctrine-cache-bundle:^1.3.1"
fi

composer update ${COMPOSER_UPDATE_ARGS:-} --with-all-dependencies

# revert changes applied by Flex recipes
git reset --hard && git clean -df
