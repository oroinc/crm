<?php

namespace OroCRM\Bundle\ContactBundle\Formatter;

class SocialUrlFormatter
{
    const PARAM = '%username%';

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

        return str_replace(self::PARAM, $username, $this->socialUrlFormat[$socialType]);
    }

    /**
     * @param string $socialType
     * @param string $socialLink
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getSocialUsername($socialType, $socialLink)
    {
        if (empty($this->socialUrlFormat[$socialType])) {
            throw new \InvalidArgumentException(sprintf('Unknown social network type "%s"', $socialType));
        }

        if (strpos($socialLink, 'http://') === 0 || strpos($socialLink, 'https://') === 0) {
            $format   = $this->socialUrlFormat[$socialType];
            $tokens   = explode(self::PARAM, $format);
            foreach ($tokens as $token) {
                $socialLink = str_replace($token, '', $socialLink);
            }

            return $socialLink;
        }

        return $socialLink;
    }
}
