<?php

namespace Oro\Bundle\NavigationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TitleIndexLoadCommand
 * Console command implementation
 *
 * @package Oro\Bundle\NavigationBundle\Command
 */
class TitleIndexUpdateCommand extends ContainerAwareCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this->setName('oro:title:index:update');
        $this->setDescription('Load "Title Templates" from annotations and config files to db');
    }

    /**
     * Runs command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        $this->update($this->getContainer()->get('router')->getRouteCollection()->all());

        $output->writeln('Completed');
    }

    /**
     * Update titles index
     *
     * @param array $routes
     */
    private function update($routes)
    {
        $toUpdate = array();

        foreach ($routes as $name => $route) {
            $requirements = $route->getRequirements();
            $method = isset($requirements['_method'])
                ?  $requirements['_method'] : 'ANY';

            if ($this->checkMethod($method) && $route->getDefault('_controller') != 'assetic.controller:render') {
                $toUpdate[$name] = $route;
            }
        }

        $this->getContainer()->get('oro_navigation.title_service')->update($toUpdate);
    }

    /**
     * Check if allowed GET method
     *
     * @param mixed $method
     * @return bool
     */
    private function checkMethod($method)
    {
        $allowed = 'GET';

        $result = (is_array($method) && in_array($allowed, $method)
                    || $method === $allowed
                    || $method === 'ANY') ? true : false;

        return $result;
    }
}
