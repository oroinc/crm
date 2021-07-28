<?php

namespace Oro\Bundle\ContactBundle\Formatter;

/**
 * Formats social URLs.
 */
class SocialUrlFormatter
{
    const PARAM = '%username%';

    /**
     * @var
     */
    protected $socialUrlFormat = array();

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

        if (str_starts_with($username, 'http://') || str_starts_with($username, 'https://')) {
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

        if (str_starts_with($socialLink, 'http://') || str_starts_with($socialLink, 'https://')) {
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
