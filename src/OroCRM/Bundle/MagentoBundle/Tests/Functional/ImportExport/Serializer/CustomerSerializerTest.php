<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\ImportExport\Serializer;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerSerializer;

class CustomerSerializerTest extends WebTestCase
{
    /**
     * @var CustomerSerializer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->initClient();

        $this->normalizer = $this->getContainer()->get('orocrm_magento.importexport.normalizer.cart');
        $this->normalizer->setSerializer($this->getContainer()->get('oro_importexport.serializer'));
    }

    /**
     * @dataProvider denormalizeDataProvider
     * @param array $data
     */
    public function testDenormalize($data)
    {
        $class = 'OroCRM\Bundle\MagentoBundle\Entity\Cart';
        $obj = $this->normalizer->denormalize(
            $data,
            $class,
            null,
            array(
                'processorAlias' => "orocrm_magento.add_or_update_customer",
                'entityName' => $class,
                'channel' => 1,
                'force' => false
            )
        );

        $processor = $this->getContainer()->get('orocrm_magento.import.strategy.cart.add_or_update');
        $processor->process($obj);
    }

    public function denormalizeDataProvider()
    {
        return array(
            array(
                json_decode(
                    file_get_contents('/tmp/cart.json'),
                    JSON_OBJECT_AS_ARRAY
                )
            )
        );
    }
}
