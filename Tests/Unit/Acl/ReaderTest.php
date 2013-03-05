<?php
namespace Oro\Bundle\UserBundle\Tests\Functional\Acl\ResourceReader\ReaderTest;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Oro\Bundle\UserBundle\Acl\ResourceReader\Reader;

class ReaderTest //extends WebTestCase
{
    /**
     * @var \Oro\Bundle\UserBundle\Acl\ResourceReader\Reader
     */
   /* private $reader;

    private $kernelMoc;

    private $annotationReader;

    private $testBundle;

    public function setUp()
    {
        $this->getKernelClass();
        $kernel = new \AppKernel("dev", true);
        $kernel->boot();
        $this->application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $this->container = $kernel->getContainer();
        $this->application->setAutoExit(false);

        if (!interface_exists('Doctrine\Common\Annotations\Reader')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->testBundle = $this->getMock(
            'Symfony\Bundle\FrameworkBundle\FrameworkBundle'
        );

        $this->kernelMoc = $this->getMock(
            'Symfony\Component\HttpKernel\KernelInterface',
            array()
        );

        $this->annotationReader = $this->container->get('annotation_reader');

        $this->reader = new Reader($this->kernelMoc, $this->annotationReader);
    }

    public function testGetResources()
    {
        $this->kernelMoc->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array($this->testBundle)));

        $this->testBundle->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(realpath(__DIR__ . '/../../../Unit/Fixture/Controller')));

        $resultAclList = $this->reader->getResources();

        $this->assertEquals(7, count($resultAclList));

        $this->assertEquals(true, isset($resultAclList['user_test_main_controller']));
        $controllerAcl = $resultAclList['user_test_main_controller'];
        $this->assertEquals(false, $controllerAcl->getParent());

        $subControllerAcl = $resultAclList['user_test_main_controller_sub_action2'];
        $this->assertEquals('user_test_main_controller', $subControllerAcl->getParent());
        $this->assertEquals('user_test_main_controller_sub_action2', $subControllerAcl->getId());
    }*/
}
