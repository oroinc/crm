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
     * @param array $contactInfoFields
     * @param object $entity
     * @param string $type
     * @param array $expected
     *
     * @dataProvider fieldsDataProvider
     */
    public function testGetQueryContactInformationFields($contactInfoFields, $definition, $entity, $type, $expected)
    {
        $queryDesigner = $this->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');
        $queryDesigner
            ->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValue($definition));

        $this->helper
            ->expects($this->once())
            ->method('getEntityContactInformationColumns')
            ->will($this->returnValue($contactInfoFields));

        $this->assertEquals(
            $expected,
            $this->provider->getQueryContactInformationFields($queryDesigner, $entity, $type)
        );
    }

    /**
     * @return array
     */
    public function fieldsDataProvider()
    {
        $email = new ContactEmail('mail@example');
        $email->setPrimary(true);
        $contact = new Contact();
        $contact->addEmail($email);

        return [
            [
                null,
                json_encode([]),
                $contact,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                [],
                json_encode([]),
                $contact,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                ['email' => 'email'],
                json_encode([]),
                $contact,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                ['mail@example']
            ],
            [
                ['email' => 'email'],
                json_encode(['columns' => [['name' => 'primaryEmail']]]),
                $contact,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                ['email' => 'email'],
                json_encode(['columns' => [['name' => 'primaryEmail']]]),
                $contact,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                ['email' => 'email'],
                json_encode(['columns' => [['name' => 'email']]]),
                $contact,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_PHONE,
                []
            ],
            [
                ['email' => 'email'],
                json_encode(['columns' => [['name' => 'email'], ['name' => 'phone']]]),
                $contact,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                ['mail@example']
            ],
        ];
    }
}
