<?php

/*
 * Defining routes in a PHP file (in contrast to XML or YAML) avoids using different configs
 * with either a "pattern" (for Symfony 2.1) or a "path" (for Symfony > 2.1) option.
 *
 * Author: Christian Raue <christian.raue@gmail.com>
 * Copyright: 2011-2015 Christian Raue
 * License: http://opensource.org/licenses/mit-license.php MIT License
 */

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

/* @var $loader \Symfony\Component\Routing\Loader\PhpFileLoader */
$settings = $loader->import("@CraueConfigBundle/Resources/config/routing/settings.xml");
$settings->addPrefix('/settings');
$routes->addCollection($settings);

$routes->add('admin_settings_start', new Route('/settings-start/modify', array(
	'_controller' => 'CraueConfigBundle:Settings:modify'
)));

return $routes;
