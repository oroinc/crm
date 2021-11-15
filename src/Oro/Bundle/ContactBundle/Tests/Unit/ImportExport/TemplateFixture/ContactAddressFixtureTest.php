<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\ImportExport\TemplateFixture;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\ImportExport\TemplateFixture\AddressTypeFixture;
use Oro\Bundle\AddressBundle\ImportExport\TemplateFixture\CountryFixture;
use Oro\Bundle\AddressBundle\ImportExport\TemplateFixture\RegionFixture;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\ImportExport\TemplateFixture\ContactAddressFixture;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateEntityRegistry;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateManager;

class ContactAddressFixtureTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactAddressFixture */
    private $fixture;

    protected function setUp(): void
    {
        $this->fixture = new ContactAddressFixture();
    }

    public function testGetEntityClass()
    {
        $this->assertEquals(ContactAddress::class, $this->fixture->getEntityClass());
    }

    public function testCreateEntity()
    {
        $this->fixture->setTemplateManager($this->getTemplateManager());

        $this->assertEquals(new ContactAddress(), $this->fixture->getEntity('Jerry Coleman'));
    }

    /**
     * @dataProvider fillEntityDataProvider
     */
    public function testFillEntityData(string $key, array $types)
    {
        $this->fixture->setTemplateManager($this->getTemplateManager());

        $address = new ContactAddress();
        [$firstName, $lastName] = explode(' ', $key, 2);

        $this->fixture->fillEntityData($key, $address);
        $this->assertEquals($firstName, $address->getFirstName());
        $this->assertEquals($lastName, $address->getLastName());
        $this->assertEquals($types, $address->getTypeNames());
    }

    public function fillEntityDataProvider(): array
    {
        return [
            'Jerry Coleman' => [
                'key' => 'Jerry Coleman',
                'types' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING]
            ],
            'John Smith' => [
                'key' => 'John Smith',
                'types' => [AddressType::TYPE_BILLING]
            ],
            'John Doo' => [
                'key' => 'John Doo',
                'types' => [AddressType::TYPE_SHIPPING]
            ],
        ];
    }

    public function testGetData()
    {
        $this->fixture->setTemplateManager($this->getTemplateManager());

        $data = $this->fixture->getData();
        $this->assertCount(1, $data);

        /** @var ContactAddress $address */
        $address = $data->current();
        $this->assertInstanceOf(ContactAddress::class, $address);
        $this->assertEquals('Jerry', $address->getFirstName());
        $this->assertEquals('Coleman', $address->getLastName());
        $this->assertEquals([AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING], $address->getTypeNames());
    }

    private function getTemplateManager(): TemplateManager
    {
        $templateManager = new TemplateManager(new TemplateEntityRegistry());
        $templateManager->addEntityRepository(new CountryFixture());
        $templateManager->addEntityRepository(new RegionFixture());
        $templateManager->addEntityRepository(new AddressTypeFixture());
        $templateManager->addEntityRepository($this->fixture);

        return $templateManager;
    }
}
