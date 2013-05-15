<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\Event;

use Oro\Bundle\NavigationBundle\Event\ResponseHashnavListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseHashnavListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\NavigationBundle\Event\ResponseHashnavListener
     */
    protected $listener;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    protected $templating;
    protected $event;
    protected $securityContext;

    public function setUp()
    {
        $this->request = new Request();
        $this->response = new Response();

        $this->event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->event->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($this->response));

        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $this->listener = new ResponseHashnavListener($this->securityContext, $this->templating);
    }

    public function testPlainRequest()
    {
        $testBody = 'test';
        $this->response->setContent($testBody);

        $this->listener->onResponse($this->event);

        $this->assertEquals($testBody, $this->response->getContent());
    }

    public function testHashRequestWOUser()
    {
        $this->request->headers->add(array('x-oro-hash-navigation' => true));
        $this->response->setStatusCode(302);

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(false));

        $this->event->expects($this->once())
            ->method('setResponse');

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->will($this->returnValue(new Response()));

        $this->listener->onResponse($this->event);
    }
}
