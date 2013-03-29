<?php

namespace Oro\Bundle\ConfigBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\UserConfigManager;

class ConfigExtension extends \Twig_Extension
{
    /**
     * @var \Oro\Bundle\ConfigBundle\Config\UserConfigManager
     */
    private $userConfigManager;

    public function __construct(UserConfigManager $userConfigManager)
    {
        $this->userConfigManager = $userConfigManager;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'get_user_value' => new \Twig_Function_Method($this, 'getUserValue'),
        );
    }

    /**
     * @param string $configName
     * @return mixed
     */
    public function getUserValue($configName)
    {
        return $this->userConfigManager->get($configName);
    }


    /**
     * Returns the name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'config_extension';
    }
}