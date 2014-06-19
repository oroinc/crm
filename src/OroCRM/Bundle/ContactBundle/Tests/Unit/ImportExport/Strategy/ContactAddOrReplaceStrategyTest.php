<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Strategy;

use Doctrine\Common\Collections\ArrayCollection;

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
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    protected function setUp()
    {
        $this->importStrategyHelper = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        $this->importStrategyHelper
            ->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em));

        $this->fieldHelper = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Field\FieldHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldHelper
            ->expects($this->atLeastOnce())
            ->method('getFields')
            ->will($this->returnValue([]));

        $this->context = $this
            ->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');

        $this->metadata = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadata
            ->expects($this->atLeastOnce())
            ->method('getIdentifierValues')
            ->will($this->returnValue([]));

        $this->em
            ->expects($this->atLeastOnce())
            ->method('getClassMetadata')
            ->will($this->returnValue($this->metadata));

        $this->repository = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->strategy = new ContactAddOrReplaceStrategy(
            $this->importStrategyHelper,
            $this->fieldHelper
        );

        $this->strategy->setImportExportContext($this->context);

        $this->registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Registry was not set
     */
    public function testRegistryNotSet()
    {
        $this->strategy->setEntityName('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $this->strategy->process($this->getEntity());
    }

    /**
     * @dataProvider entityProvider
     */
    public function testProcess()
    {
        $class = 'OroCRM\Bundle\ContactBundle\Entity\Contact';

        $this->registry
            ->expects($this->exactly(3))
            ->method('getManagerForClass')
            ->will($this->returnValue($this->em));

        $this->repository
            ->expects($this->exactly(3))
            ->method('findOneBy')
            ->with($this->isType('array'))
            ->will(
                $this->returnCallback(
                    function ($criteria) {
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

        $this->strategy->setRegistry($this->registry);
        $this->strategy->setEntityName($class);
        $this->strategy->process($this->getEntity());
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
     * @return Contact
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

        return $contact;
    }
}
