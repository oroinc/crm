<?php
/**
 * @OroScript("OROCrm Install")
 */

/** @var $container \Symfony\Component\DependencyInjection\ContainerInterface */
// Temporary fix till EmailAddress will be moved to the cache folder
$cacheDir = $container->getParameter('oro_email.entity.cache_dir');
$cacheNamespace = $container->getParameter('oro_email.entity.cache_namespace');
$proxyNameTemplate = $container->getParameter('oro_email.entity.proxy_name_template');

//clear cache file
$entityCacheDir = sprintf('%s/%s', $cacheDir, str_replace('\\', '/', $cacheNamespace));
$className = sprintf($proxyNameTemplate, 'EmailAddress');
unlink(sprintf('%s/%s.php', $entityCacheDir, $className));

//warmup cache
$container->get('oro_email.entity.cache.warmer')->warmUp($cacheDir);
