<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__.'/../vendor/autoload.php';

// needed for Symfony < 2.7, see https://travis-ci.org/craue/CraueConfigBundle/builds/81349322
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
