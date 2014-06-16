<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Formatter;

use OroCRM\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use OroCRM\Bundle\ContactBundle\Model\Social;

class SocialUrlFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $format
     * @param string $link
     * @param string $username
     *
     * @dataProvider urlProvider
     */
    public function testGetSocialUrl($format, $link, $username)
    {
        $formatter = new SocialUrlFormatter($format);
        $this->assertEquals($link, $formatter->getSocialUrl(Social::TWITTER, $username));
    }

    /**
     * @return array
     */
    public function urlProvider()
    {
        return [
            [
                [Social::TWITTER => 'http://twitter.domain/%username%'],
                'http://twitter.domain/me',
                'me'
            ],
            [
                [Social::TWITTER => 'http://twitter.domain/%username%'],
                'http://twitter.domain/me',
                'http://twitter.domain/me'
            ],
            [
                [Social::TWITTER => 'http://twitter.domain/%username%'],
                'https://twitter.domain/me',
                'https://twitter.domain/me'
            ]
        ];
    }

    /**
     * @param array $format
     * @param string $link
     * @param string $username
     *
     * @dataProvider usernameProvider
     */
    public function testGetSocialUsername($format, $link, $username)
    {
        $formatter = new SocialUrlFormatter($format);
        $this->assertEquals(
            $username,
            $formatter->getSocialUsername(Social::TWITTER, $link)
        );
    }

    /**
     * @return array
     */
    public function usernameProvider()
    {
        return [
            [
                [Social::TWITTER => 'http://twitter.domain/%username%'],
                'http://twitter.domain/username',
                'username'
            ],
            [
                [Social::TWITTER => 'http://twitter.domain/%username%/test'],
                'http://twitter.domain/username/test',
                'username'
            ],
            [
                [Social::TWITTER => 'https://twitter.domain/%username%/test'],
                'https://twitter.domain/username/test',
                'username'
            ],
            [
                [Social::TWITTER => 'http://twitter.domain/%username%'],
                'username',
                'username'
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown social network type "type"
     */
    public function testGetSocialUrlNoSocial()
    {
        $formatter = new SocialUrlFormatter(array());
        $formatter->getSocialUrl('type', 'me');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown social network type "type"
     */
    public function testGetSocialUsernameNoSocial()
    {
        $formatter = new SocialUrlFormatter(array());
        $formatter->getSocialUsername('type', 'me');
    }
}
