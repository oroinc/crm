<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\ImportExport\CustomerReverseSync\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;

class TransportExtensionAwareFixture extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $transport = new MagentoSoapTransport();
        $transport->setAdminUrl('http://localhost/magento/admin');
        $transport->setApiKey('key');
        $transport->setApiUser('user');
        $transport->setIsExtensionInstalled(true);
        $transport->setIsWsiMode(false);
        $transport->setWebsiteId('1');
        $transport->setWsdlUrl('http://localhost/magento/api/v2_soap?wsdl=1');
        $transport->setWebsites(['id' => 1, 'label' => 'Website ID: 1, Stores: English, French, German']);

        $manager->persist($transport);
        $manager->flush();
    }
}
