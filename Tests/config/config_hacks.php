<?php

use Symfony\Component\HttpKernel\Kernel;

// TODO remove as soon as Symfony >= 6 is required
if (Kernel::VERSION_ID >= 40300 && Kernel::VERSION_ID < 60000) {
	$container->loadFromExtension('framework', [
		'router' => [
			'utf8' => true,
		],
	]);
}

// TODO in config_cache_SymfonyCacheComponent_redis.yml, replace '%REDIS_DSN%' by '%env(REDIS_DSN)%' as soon as Symfony >= 4.0 is required (and remove this hack)
if (!empty($_ENV['REDIS_DSN'])) {
	$container->setParameter('REDIS_DSN', $_ENV['REDIS_DSN']);
}
