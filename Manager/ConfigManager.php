<?php

namespace Oro\Bundle\ConfigBundle\Manager;

use Symfony\Component\HttpFoundation\Session\Session;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Entity\User;

class ConfigManager
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var array
     */
    protected $settings;

    public function __construct(ObjectManager $om, Session $session, $settings = array())
    {
        $this->om       = $om;
        $this->session  = $session;
        $this->settings = $settings;
    }

    /**
     *
     * @param  string $name Setting name
     * @return mixed
     */
    public function get($name, User $user = null)
    {
        $name    = explode('.', $name);
        $setting = $this->settings[$name[0]];
        $setting = isset($setting[$name[1]]) ? $setting[$name[1]] : null;

        return is_array($setting) ? $setting['value'] : $setting;
    }
}
