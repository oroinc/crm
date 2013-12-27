<?php
/**
 * @OroScript("OROCrm Install")
 */

/** @var $container \Symfony\Component\DependencyInjection\ContainerInterface */
// Temporary fix till EmailAddress will be moved to the cache folder
$cacheDir = $container->getParameter('oro_email.entity.cache_dir');

$container->get('oro_email.entity.cache.clearer')->force($cacheDir);
$container->get('oro_email.entity.cache.warmer')->warmUp($cacheDir);
