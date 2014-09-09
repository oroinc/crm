<?php
namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MarketingListBundle\Form\Type\MarketingListTypeUnsubscribedItemType;

class MarketingListTypeUnsubscribedItemTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListTypeUnsubscribedItemType
     */
    protected $type;

    /**
     * Setup test env
     */
    protected function setUp()
    {
        $this->type = new MarketingListTypeUnsubscribedItemType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(2))
            ->method('add')
            ->will($this->returnSelf());

        $builder->expects($this->at(0))
            ->method('add')
            ->with('entityId', 'integer', ['required' => true]);

        $builder->expects($this->at(1))
            ->method('add')
            ->with(
                'marketingList',
                'entity',
                [
                    'class'    => 'OroCRM\Bundle\MarketingListBundle\Entity\MarketingList',
                    'required' => true
                ]
            );

        $this->type->buildForm($builder, array());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_marketing_list_unsubscribed_item', $this->type->getName());
    }
}
