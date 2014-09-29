<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Strategy;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\ImportExport\Strategy\ContactAddOrReplaceStrategy;

class ContactAddOrReplaceStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactAddOrReplaceStrategy
     */
    protected $strategy;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $importStrategyHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $databaseHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    protected function setUp()
    {
        $this->importStrategyHelper = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldHelper = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Field\FieldHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldHelper
            ->expects($this->atLeastOnce())
            ->method('getFields')
            ->will($this->returnValue([]));

        $this->databaseHelper = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Field\DatabaseHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this
            ->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');

        $this->metadata = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')
            ->disableOriginalConstructor()
            ->getMock();

        $this->strategy = new ContactAddOrReplaceStrategy(
            $this->importStrategyHelper,
            $this->fieldHelper,
            $this->databaseHelper
        );

        $this->strategy->setImportExportContext($this->context);

        $this->registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
    }

    /**
     * @dataProvider entityProvider
     */
    public function testProcess()
    {
        $class = 'OroCRM\Bundle\ContactBundle\Entity\Contact';

        $this->databaseHelper
            ->expects($this->exactly(3))
            ->method('findOneBy')
            ->with($this->isType('string'), $this->isType('array'))
            ->will(
                $this->returnCallback(
                    function ($class, $criteria) {
                        $propertyMap = [
                            'iso2Code'     => 'Oro\Bundle\AddressBundle\Entity\Country',
                            'combinedCode' => 'Oro\Bundle\AddressBundle\Entity\Region',
                            'name'         => 'Oro\Bundle\AddressBundle\Entity\AddressType',
                        ];

                        foreach (array_keys($criteria) as $property) {
                            if (!empty($propertyMap[$property])) {
                                return $this
                                    ->getMockBuilder($propertyMap[$property])
                                    ->disableOriginalConstructor()
                                    ->getMock();
                            }
                        }

                        return null;
                    }
                )
            );

        $itemData = array(
            'addresses' => array(
                array('firstName' => 'John', 'types' => array(AddressType::TYPE_BILLING)),
                array('firstName' => 'John'),
            )
        );
        $expectedItemData = $itemData;
        $expectedItemData['addresses'][1]['types'] = array();

        $this->context->expects($this->any())->method('getValue')->with('itemData')
            ->will($this->returnValue($itemData));
        $this->context->expects($this->once())->method('setValue')
            ->with($this->isType('string'), $this->isType('array'))
            ->will(
                $this->returnCallback(
                    function ($key, $value) use ($expectedItemData) {
                        $this->assertEquals('itemData', $key);
                        $this->assertEquals($expectedItemData, $value);
                    }
                )
            );
        $contact = $this->getEntity();
        $contact->expects($this->once())->method('setPrimaryAddress')
            ->with($this->isInstanceOf('OroCRM\Bundle\ContactBundle\Entity\ContactAddress'));
        $contact->expects($this->once())->method('setPrimaryEmail')
            ->with($this->isInstanceOf('OroCRM\Bundle\ContactBundle\Entity\ContactEmail'));
        $contact->expects($this->once())->method('setPrimaryPhone')
            ->with($this->isInstanceOf('OroCRM\Bundle\ContactBundle\Entity\ContactPhone'));

        $this->strategy->setEntityName($class);
        $this->strategy->process($contact);
    }

    /**
     * @return array
     */
    public function entityProvider()
    {
        return [
            'empty'  => [$this->getEntity(false)],
            'values' => [$this->getEntity()],
        ];
    }

    /**
     * @param bool $returnValues
     * @return Contact|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEntity($returnValues = true)
    {
        $contact = $this
            ->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $contactAddress = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\ContactAddress');

        $contact
            ->expects($this->any())
            ->method('getAddresses')
            ->will(
                $this->returnValue(
                    new ArrayCollection([$contactAddress])
                )
            );

        $country = $this
            ->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->getMock();

        $contactAddress
            ->expects($this->any())
            ->method('getCountry')
            ->will($this->returnValue($returnValues ? $country : null));

        $region = $this
            ->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Region')
            ->disableOriginalConstructor()
            ->getMock();

        $contactAddress
            ->expects($this->any())
            ->method('getRegion')
            ->will($this->returnValue($returnValues ? $region : null));

        $type = $this
            ->getMockBuilder('Oro\Bundle\AddressBundle\Entity\AddressType')
            ->disableOriginalConstructor()
            ->getMock();

        $contactAddress
            ->expects($this->any())
            ->method('getTypes')
            ->will(
                $this->returnValue(
                    $returnValues ? new ArrayCollection([$type]) : null
                )
            );

        $contactEmail = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\ContactEmail');
        $contact
            ->expects($this->any())
            ->method('getEmails')
            ->will(
                $this->returnValue(
                    new ArrayCollection([$contactEmail])
                )
            );

        $contactPhone = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\ContactPhone');
        $contact
            ->expects($this->any())
            ->method('getPhones')
            ->will(
                $this->returnValue(
                    new ArrayCollection([$contactPhone])
                )
            );

        return $contact;
    }
}
