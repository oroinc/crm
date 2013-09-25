<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Formatter;

use OroCRM\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use OroCRM\Bundle\ContactBundle\Model\Social;

class SocialUrlFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSocialUrl()
    {
        $format = array(
            Social::TWITTER => 'http://twitter.domain/%username%'
        );

        $formatter = new SocialUrlFormatter($format);
        $this->assertEquals('http://twitter.domain/me', $formatter->getSocialUrl(Social::TWITTER, 'me'));
        $this->assertEquals(
            'http://twitter.domain/me',
            $formatter->getSocialUrl(Social::TWITTER, 'http://twitter.domain/me')
        );
        $this->assertEquals(
            'https://twitter.domain/me',
            $formatter->getSocialUrl(Social::TWITTER, 'https://twitter.domain/me')
        );
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
}
