<?php

namespace Oro\Bundle\ContactBundle\ImportExport\TemplateFixture;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\ImportExport\TemplateFixture\AddressFixture;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;

class ContactAddressFixture extends AddressFixture implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'Oro\Bundle\ContactBundle\Entity\ContactAddress';
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        return new ContactAddress();
    }

    /**
     * @param string $key
     * @param ContactAddress $entity
     */
    public function fillEntityData($key, $entity)
    {
        $typeRepository = $this->templateManager
            ->getEntityRepository('Oro\Bundle\AddressBundle\Entity\AddressType');

        $billingType = $typeRepository->getEntity(AddressType::TYPE_BILLING);
        $shippingType = $typeRepository->getEntity(AddressType::TYPE_SHIPPING);

        switch ($key) {
            case 'Jerry Coleman':
                $entity->addType($billingType)
                    ->addType($shippingType);
                break;
            case 'John Smith':
                $entity->addType($billingType);
                break;
            case 'John Doo':
                $entity->addType($shippingType);
                break;
        }

        parent::fillEntityData($key, $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Jerry Coleman');
    }
}
