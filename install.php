<?php
/**
 * @OroScript("OroCRM Install")
 */

/** @var $container \Symfony\Component\DependencyInjection\ContainerInterface */
/** @var $commandExecutor \Oro\Bundle\InstallerBundle\CommandExecutor */
// Temporary fix till EmailAddress will be moved to the cache folder
$container->get('oro_email.entity.cache.clearer')->forceClear();
$container->get('oro_email.entity.cache.warmer')->warmUp('');
$commandExecutor
    ->runCommand('doctrine:schema:update', array('--force' => true, '--process-isolation' => true))
    ->runCommand('oro:search:create-index');
