<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Provider;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class ContactInformationFieldsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactInformationFieldsProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    protected function setUp()
    {
        $this->helper = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new ContactInformationFieldsProvider($this->helper);
    }

    /**
     * @param array $contactInfoFields
     * @param array $definition
     * @param string $type
     * @param array $expected
     *
     * @dataProvider queryFieldsDataProvider
     */
    public function testGetQueryTypedFields($contactInfoFields, $definition, $type, $expected)
    {
        $queryDesigner = $this->getMockBuilder('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queryDesigner->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue('\stdClass'));
        $this->assertGetQueryTypedFieldsCalls($queryDesigner, $definition, $contactInfoFields);

        $this->assertEquals(
            $expected,
            $this->provider->getQueryTypedFields($queryDesigner, $type)
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject$queryDesigner
     * @param array $definition
     * @param array $contactInfoFields
     */
    protected function assertGetQueryTypedFieldsCalls($queryDesigner, $definition, $contactInfoFields)
    {
        $queryDesigner
            ->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValue($definition));

        $this->helper
            ->expects($this->once())
            ->method('getEntityContactInformationColumns')
            ->will($this->returnValue($contactInfoFields));
    }

    /**
     * @return array
     */
    public function queryFieldsDataProvider()
    {
        return [
            [
                null,
                json_encode([]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                [],
                json_encode([]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                ['email' => 'email'],
                json_encode([]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                ['email']
            ],
            [
                ['email' => 'email'],
                json_encode(['columns' => [['name' => 'primaryEmail']]]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                ['email' => 'email'],
                json_encode(['columns' => [['name' => 'primaryEmail']]]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                ['email' => 'email'],
                json_encode(['columns' => [['name' => 'email']]]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_PHONE,
                []
            ],
            [
                ['email' => 'email'],
                json_encode(['columns' => [['name' => 'email'], ['name' => 'phone']]]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                ['email']
            ],
        ];
    }

    /**
     * @param array $contactInfoFields
     * @param string $type
     * @param array $expected
     *
     * @dataProvider fieldsDataProvider
     */
    public function testGetEntityTypedFields($contactInfoFields, $type, $expected)
    {
        $entity = '\stdClass';
        $this->assertGetEntityTypedFieldsCalls($entity, $contactInfoFields);

        $this->assertEquals(
            $expected,
            $this->provider->getEntityTypedFields($entity, $type)
        );
    }

    protected function assertGetEntityTypedFieldsCalls($entity, $contactInfoFields)
    {
        $this->helper
            ->expects($this->once())
            ->method('getEntityContactInformationColumns')
            ->with($entity)
            ->will($this->returnValue($contactInfoFields));
    }

    public function testGetTypedFieldsValues()
    {
        $entity = new \stdClass();
        $entity->email = 'test';
        $entity->other = 'other';

        $expected = ['test'];
        $this->assertEquals($expected, $this->provider->getTypedFieldsValues(['email'], $entity));
    }

    /**
     * @return array
     */
    public function fieldsDataProvider()
    {
        return [
            [
                null,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                [],
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                ['email' => 'email'],
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_PHONE,
                []
            ],
            [
                ['email' => 'email'],
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                ['email']
            ],
        ];
    }

    /**
     * @param array $contactInfoFields
     * @param string $type
     * @param array $expected
     *
     * @dataProvider fieldsDataProvider
     */
    public function testGetMarketingListTypedFieldsManual($contactInfoFields, $type, $expected)
    {
        $entity = '\stdClass';
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(true));
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue('\stdClass'));
        $this->assertGetEntityTypedFieldsCalls($entity, $contactInfoFields);

        $this->assertEquals($expected, $this->provider->getMarketingListTypedFields($marketingList, $type));
    }

    /**
     * @param array $contactInfoFields
     * @param array $definition
     * @param string $type
     * @param array $expected
     *
     * @dataProvider queryFieldsDataProvider
     */
    public function testGetMarketingListTypedFieldsNonManual($contactInfoFields, $definition, $type, $expected)
    {
        $queryDesigner = $this->getMockBuilder('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queryDesigner->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue('\stdClass'));
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(false));
        $marketingList->expects($this->once())
            ->method('getSegment')
            ->will($this->returnValue($queryDesigner));
        $this->assertGetQueryTypedFieldsCalls($queryDesigner, $definition, $contactInfoFields);

        $this->assertEquals($expected, $this->provider->getMarketingListTypedFields($marketingList, $type));
    }
}
