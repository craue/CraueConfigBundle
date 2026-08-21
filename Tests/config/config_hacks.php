<?php

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @var $container \Symfony\Component\DependencyInjection\ContainerBuilder
 */

// TODO remove as soon as Symfony >= 6 is required
if (Kernel::VERSION_ID < 60000) {
	$container->loadFromExtension('framework', [
		'router' => [
			'utf8' => true,
		],
	]);
}

// TODO remove as soon as Symfony >= 6 is required
if (Kernel::VERSION_ID >= 50200 && Kernel::VERSION_ID < 60000) {
	$container->loadFromExtension('framework', [
		'form' => [
			'legacy_error_messages' => false,
		],
	]);
}

// TODO remove as soon as Symfony >= 7 is required, see https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.4.md#frameworkbundle
if (Kernel::VERSION_ID >= 60400 && Kernel::VERSION_ID < 70000) {
	$container->loadFromExtension('framework', [
		'handle_all_throwables' => true,
		'php_errors' => [
			'log' => true,
		],
		'session' => [
			'cookie_secure' => 'auto',
			'cookie_samesite' => 'lax',
		],
		'validation' => [
			'email_validation_mode' => 'html5',
		],
	]);
}

// TODO remove as soon as Symfony >= 7 is required, see https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.1.md#frameworkbundle
if (Kernel::VERSION_ID < 70000) {
	$container->loadFromExtension('framework', [
		'http_method_override' => false,
	]);
}

// TODO remove as soon as Symfony >= 8 is required
// "Since symfony/framework-bundle 7.3: Not setting the "property_info.with_constructor_extractor" option explicitly is deprecated because its default value will change in version 8.0."
if (Kernel::VERSION_ID >= 70300 && Kernel::VERSION_ID < 80000) {
	$container->loadFromExtension('framework', [
		'property_info' => [
			'with_constructor_extractor' => true,
		],
	]);
}

// TODO remove as soon as Symfony >= 8.1 is required
// "Since symfony/framework-bundle 7.3: Not setting the "framework.profiler.collect_serializer_data" config option to "true" is deprecated."
// "Since symfony/framework-bundle 8.1: Setting the "framework.profiler.collect_serializer_data" configuration option is deprecated. It will be removed in version 9.0."
if (Kernel::VERSION_ID >= 70300 && Kernel::VERSION_ID < 80100) {
	$container->loadFromExtension('framework', [
		'profiler' => [
			'collect_serializer_data' => true,
		],
	]);
}

// TODO remove as soon as doctrine/doctrine-bundle >= 3.1 is required
if (InstalledVersions::satisfies(new VersionParser(), 'doctrine/doctrine-bundle', '<3.1')) {
	if (\PHP_VERSION_ID >= 80400 && InstalledVersions::satisfies(new VersionParser(), 'doctrine/orm', '>=3.4')) {
		// "Native lazy objects are not supported with your installed version of the ORM. Please upgrade to "doctrine/orm >= 3.4"."
		// "Since doctrine/doctrine-bundle 3.1: The "enable_native_lazy_objects" option is deprecated and will be removed in DoctrineBundle 4.0, as native lazy objects are now always enabled."
		$container->loadFromExtension('doctrine', [
			'orm' => [
				'enable_native_lazy_objects' => true,
			],
		]);
	} elseif (InstalledVersions::satisfies(new VersionParser(), 'doctrine/doctrine-bundle', '>=2.11')
			&& InstalledVersions::satisfies(new VersionParser(), 'symfony/var-exporter', '>=6.2')) {
		// "Since doctrine/doctrine-bundle 2.11: Not setting "doctrine.orm.enable_lazy_ghost_objects" to true is deprecated."
		$container->loadFromExtension('doctrine', [
			'orm' => [
				'enable_lazy_ghost_objects' => true,
			],
		]);
	}
}

// TODO remove as soon as doctrine/doctrine-bundle >= 3.1 is required
if (InstalledVersions::satisfies(new VersionParser(), 'doctrine/doctrine-bundle', '<3.1')) {
	// "Since doctrine/doctrine-bundle 3.1: The "enable_native_lazy_objects" option is deprecated and will be removed in DoctrineBundle 4.0, as native lazy objects are now always enabled."
	$container->loadFromExtension('doctrine', [
		'orm' => [
			'auto_generate_proxy_classes' => '%kernel.debug%',
		],
	]);
}

// TODO remove as soon as doctrine/doctrine-bundle >= 2.12 is required
if (InstalledVersions::satisfies(new VersionParser(), 'doctrine/doctrine-bundle', '>=2.12,<3')) {
	// "Since doctrine/doctrine-bundle 2.12: The default value of "doctrine.orm.controller_resolver.auto_mapping" will be changed from `true` to `false`. Explicitly configure `true` to keep existing behaviour."
	// "Since doctrine/doctrine-bundle 3.1: The "doctrine.orm.controller_resolver.auto_mapping" option is deprecated and will be removed in DoctrineBundle 4.0, as it only accepts `false` since 3.0."
	$container->loadFromExtension('doctrine', [
		'orm' => [
			'controller_resolver' => [
				'auto_mapping' => false,
			],
		],
	]);
}
