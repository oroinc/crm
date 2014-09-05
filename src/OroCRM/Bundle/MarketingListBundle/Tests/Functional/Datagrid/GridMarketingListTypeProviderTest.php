<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Functional\Datagrid;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MarketingListBundle\Datagrid\GridMarketingListTypeProvider;

/**
 * @dbIsolation
 */
class GridMarketingListTypeProviderTest extends WebTestCase
{
    /**
     * @var GridMarketingListTypeProvider
     */
    protected $provider;

    /**
     * @var Registry
     */
    protected $registry;

    protected function setUp()
    {
        $this->initClient();

        $this->registry = $this->getContainer()->get('doctrine');
        $this->provider = new GridMarketingListTypeProvider($this->registry);
    }

    public function testGetListTypeChoices()
    {
        $types = $this->provider->getListTypeChoices();
        $this->assertInternalType('array', $types);
        foreach ($types as $name => $label) {
            $this->assertInternalType('string', $name);
            $this->assertInternalType('string', $label);
        }
    }
}
