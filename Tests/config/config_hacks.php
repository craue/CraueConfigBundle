<?php

use Symfony\Component\HttpKernel\Kernel;

/**
 * @var $container \Symfony\Component\DependencyInjection\ContainerBuilder
 */

// TODO remove as soon as Symfony >= 6 is required
if (Kernel::VERSION_ID >= 40300 && Kernel::VERSION_ID < 60000) {
	$container->loadFromExtension('framework', [
		'router' => [
			'utf8' => true,
		],
	]);
}

// TODO put back into config.yml as soon as Symfony >= 5.3 is required, see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.3.md#frameworkbundle
$container->loadFromExtension('framework', [
	'session' => Kernel::VERSION_ID >= 50300 ? [
		'storage_factory_id' => 'session.storage.factory.mock_file',
	] : [
		'storage_id' => 'session.storage.mock_file',
	],
]);

// TODO remove as soon as Symfony >= 6 is required
if (Kernel::VERSION_ID >= 50200 && Kernel::VERSION_ID < 60000) {
	$container->loadFromExtension('framework', [
		'form' => [
			'legacy_error_messages' => false,
		],
	]);
}

// TODO remove as soon as Symfony >= 7 is required
if (Kernel::VERSION_ID < 70000) {
	$container->loadFromExtension('framework', [
		'http_method_override' => false,
	]);
}
