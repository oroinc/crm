<?php
/**
 * @OroScript("OroCRM Install")
 */

/** @var $container \Symfony\Component\DependencyInjection\ContainerInterface */
// Temporary fix till EmailAddress will be moved to the cache folder
$container->get('oro_email.entity.cache.clearer')->forceClear();
$container->get('oro_email.entity.cache.warmer')->warmUp('');
