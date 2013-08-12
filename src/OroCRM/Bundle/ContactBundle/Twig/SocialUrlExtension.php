<?php

namespace OroCRM\Bundle\ContactBundle\Twig;

use OroCRM\Bundle\ContactBundle\Formatter\SocialUrlFormatter;

class SocialUrlExtension extends \Twig_Extension
{
    /**
     * @var SocialUrlFormatter
     */
    protected $socialUrlFormatter;

    /**
     * @param SocialUrlFormatter $socialUrlFormatter
     */
    public function __construct(SocialUrlFormatter $socialUrlFormatter)
    {
        $this->socialUrlFormatter = $socialUrlFormatter;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'orocrm_contact_social_url';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('oro_social_url', array($this, 'getSocialUrl')),
        );
    }

    /**
     * @param string $socialType
     * @param string $username
     * @return string
     */
    public function getSocialUrl($socialType, $username)
    {
        if (!$socialType || !$username) {
            return '#';
        }

        return $this->socialUrlFormatter->getSocialUrl($socialType, $username);
    }
}
