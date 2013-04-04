<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Menu\Matcher\Voter;

use Oro\Bundle\NavigationBundle\Menu\Matcher\Voter;
use Symfony\Component\HttpFoundation\Request;

class NavigationItemBuilderBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function testUriVoterConstruct()
    {
        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $itemMock = $this->getMock('Knp\Menu\ItemInterface');

        $uri = 'test.uri';

        $containerMock->expects($this->once())
                      ->method('get')
                      ->with($this->equalTo('request'))
                      ->will($this->returnValue(Request::create($uri)));

        $itemMock->expects($this->exactly(2))
            ->method('getUri')
            ->will($this->returnValue($uri));

        $voter = new Voter\RequestVoter($containerMock);

        $this->assertTrue($voter->matchItem($itemMock));
    }
}
