#!/bin/bash

set -euv

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
			# ensure the given version is installed for all components - this is needed at least for (PHP 7.3 + Symfony 4.4) & (PHP 7.4 + Symfony 5.4)
			# TODO remove as soon as PHP >= 8 is required
			if [ "$(php -r 'echo PHP_MAJOR_VERSION;')" = "7" ]; then
				composer require --no-update "symfony/symfony:${SYMFONY_VERSION}"
			fi
		fi
esac

if [ -n "${WITH_DOCTRINE_CACHE_BUNDLE:-}" ]; then
	composer require --no-update --dev "doctrine/doctrine-cache-bundle:^1.3.1"
fi

composer update ${COMPOSER_UPDATE_ARGS:-} --with-all-dependencies

# revert changes applied by Flex recipes
git reset --hard && git clean -df
