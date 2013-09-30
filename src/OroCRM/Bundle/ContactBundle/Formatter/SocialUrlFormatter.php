<?php

namespace OroCRM\Bundle\ContactBundle\Formatter;

class SocialUrlFormatter
{
    /**
     * @var
     */
    protected $socialUrlFormat = array();

    /**
     * @param array $socialUrlFormat
     */
    public function __construct(array $socialUrlFormat)
    {
        $this->socialUrlFormat = $socialUrlFormat;
    }

    /**
     * @param string $socialType
     * @param string $username
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getSocialUrl($socialType, $username)
    {
        if (empty($this->socialUrlFormat[$socialType])) {
            throw new \InvalidArgumentException(sprintf('Unknown social network type "%s"', $socialType));
        }

        if (strpos($username, 'http://') === 0 || strpos($username, 'https://') === 0) {
            return $username;
        }

        return str_replace('%username%', $username, $this->socialUrlFormat[$socialType]);
    }
}
