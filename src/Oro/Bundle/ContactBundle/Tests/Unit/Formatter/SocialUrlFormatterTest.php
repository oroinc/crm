<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Formatter;

use Oro\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use Oro\Bundle\ContactBundle\Model\Social;

class SocialUrlFormatterTest extends \PHPUnit\Framework\TestCase
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

    public function urlProvider(): array
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

    public function usernameProvider(): array
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

    public function testGetSocialUrlNoSocial()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown social network type "type"');

        $formatter = new SocialUrlFormatter([]);
        $formatter->getSocialUrl('type', 'me');
    }

    public function testGetSocialUsernameNoSocial()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown social network type "type"');

        $formatter = new SocialUrlFormatter([]);
        $formatter->getSocialUsername('type', 'me');
    }
}
