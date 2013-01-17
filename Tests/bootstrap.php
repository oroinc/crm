<?php
/*
$loader = __DIR__.'/../vendor/autoload.php';
if (!file_exists($loader)) {
    throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"');
}

require_once $loader;

use Doctrine\Common\Annotations\AnnotationRegistry;
*/


/*
$loader->add( 'YOURNAMESPACE', __DIR__.'/../vendor/YOURVENDOR/src' );

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));


$loader->registerNamespaces(array(
    //...
    'Doctrine\\Tests'                => __DIR__.'/../vendor/doctrine/tests',
));
*/



$loader = require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/src/Symfony/Component/Locale/Resources/stubs/functions.php';
}

$loader->add( 'Doctrine\\Tests', __DIR__.'/../vendor/doctrine/orm/tests' );

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));