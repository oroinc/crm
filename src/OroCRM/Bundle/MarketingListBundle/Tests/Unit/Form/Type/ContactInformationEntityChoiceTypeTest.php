<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MarketingListBundle\Form\Type\ContactInformationEntityChoiceType;

class ContactInformationEntityChoiceTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $provider;

    /**
     * @var ContactInformationEntityChoiceType
     */
    protected $type;

    protected function setUp()
    {
        $this->provider = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new ContactInformationEntityChoiceType($this->provider);
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_marketing_list_contact_information_entity_choice', $this->type->getName());
    }
}
