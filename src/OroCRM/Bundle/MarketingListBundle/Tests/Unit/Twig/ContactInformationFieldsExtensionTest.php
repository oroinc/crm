<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Twig;

use OroCRM\Bundle\MarketingListBundle\Twig\ContactInformationFieldsExtension;

class ContactInformationFieldsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var ContactInformationFieldsExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->helper = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new ContactInformationFieldsExtension($this->helper);
    }

    protected function tearDown()
    {
        unset($this->extension);
        unset($this->helper);
    }

    public function testGetName()
    {
        $this->assertEquals(ContactInformationFieldsExtension::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        $expectedFunctions = array(
            'get_contact_information_fields_info'
        );

        /** @var \Twig_SimpleFunction $function */
        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $this->assertContains($function->getName(), $expectedFunctions);
        }
    }

    public function testGetContactInformationFieldsInfoNoEntity()
    {
        $this->helper->expects($this->never())
            ->method($this->anything());
        $this->assertEmpty($this->extension->getContactInformationFieldsInfo(null));
    }

    public function testGetContactInformationFieldsInfo()
    {
        $entity = '\stdClass';
        $contactInformation = array(array('name' => 'test'));
        $this->helper->expects($this->once())
            ->method('getEntityContactInformationColumnsInfo')
            ->with($entity)
            ->will($this->returnValue($contactInformation));
        $this->assertEquals($contactInformation, $this->extension->getContactInformationFieldsInfo($entity));
    }
}
