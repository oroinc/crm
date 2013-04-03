<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\Event;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;
use Oro\Bundle\NavigationBundle\Event\ResponseHistoryListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseHistoryListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ResponseHistoryListener
     */
    protected $listener;

    /**
     * @var \Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory
     */
    protected $factory;

    public function setUp()
    {
        $this->factory = $this->getMock('Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory');
        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $user = new User();
        $user->setEmail('some@email.com');

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->exactly(2))
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->securityContext->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Entity\Repository\HistoryItemRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $values = array(
            'title' => 'Some Title',
            'url'   => 'Some Url',
            'user'  => $user,
        );

        $item = new NavigationHistoryItem($values);

        $repository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($item));

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem'))
            ->will($this->returnValue($repository));

        $this->listener = new ResponseHistoryListener($this->factory, $this->securityContext, $this->em);
    }

    public function testOnResponse()
    {
        $request = Request::create(
            'test.uri',
            'GET',
            array(
                '_format' => 'html',
                '_route'  => 'test',
            )
        );

        $response = new Response('', 200);

        $this->listener->onResponse($this->getEventMock($request, $response));
    }


    /**
     * Get the mock of the GetResponseEvent and FilterResponseEvent.
     *
     * @param \Symfony\Component\HttpFoundation\Request        $request
     * @param null|\Symfony\Component\HttpFoundation\Response  $response
     * @param string                                           Event type
     *
     * @return mixed
     */
    private function getEventMock($request, $response, $type = 'Symfony\Component\HttpKernel\Event\FilterResponseEvent')
    {
        $event = $this->getMockBuilder($type)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $event->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        return $event;
    }
}
