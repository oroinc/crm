<?php
$loader = require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/src/Symfony/Component/Locale/Resources/stubs/functions.php';
}

$loader->add( 'Doctrine\\Tests', __DIR__.'/../vendor/doctrine/orm/tests' );

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));