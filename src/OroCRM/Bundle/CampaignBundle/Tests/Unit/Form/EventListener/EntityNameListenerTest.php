<?php
namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormEvents;

use OroCRM\Bundle\CampaignBundle\Form\EventListener\EntityNameListener;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class EntityNameListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var EntityNameListener */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    protected function setUp()
    {
        $this->registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');

        $this->listener = new EntityNameListener($this->registry);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertArrayHasKey(FormEvents::PRE_SUBMIT, $this->listener->getSubscribedEvents());
    }

    /**
     * @dataProvider formDataProvider
     */
    public function testPreSubmit(array $data, $entity, $expected)
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $repository = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($entity));

        $this->registry
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        if (!is_null($expected)) {
            $event
                ->expects($this->once())
                ->method('setData')
                ->with($this->equalTo($expected));
        }

        $this->listener->preSubmit($event);
    }

    /**
     * @return array
     */
    public function formDataProvider()
    {
        $marketingList = new MarketingList();
        $marketingList->setEntity('\stdClass');

        return [
            [[], null, null],
            [['marketingList' => '1'], null, null],
            [['marketingList' => '1'], new MarketingList(), null],
            [['marketingList' => '1'], $marketingList, ['marketingList' => '1', 'entityName' => '\stdClass']],
        ];
    }
}
