<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\ImportExport\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;
use Oro\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;

class ChannelHelperTest extends OrmTestCase
{
    const TEST_INTEGRATION_ID_WITH_CHANNEL    = 1;
    const TEST_INTEGRATION_ID_WITHOUT_CHANNEL = 2;
    const TEST_CHANNEL_ID                     = 2;

    /** @var ChannelHelper */
    protected $helper;

    /** @var EntityManager */
    protected $em;

    protected function setUp()
    {
        $this->em = $this->getTestEntityManager();

        $config         = $this->em->getConfiguration();
        $metadataDriver = new AnnotationDriver(
            new AnnotationReader(),
            'Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity'
        );
        $config->setMetadataDriverImpl($metadataDriver);
        $config->setEntityNamespaces(['OroChannelBundle' => 'Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity']);

        $registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->em));

        $this->helper = new ChannelHelper($registry);
    }

    protected function tearDown()
    {
        unset($this->em, $this->helper);
    }

    /**
     * @dataProvider getChannelDataProvider
     *
     * @param int            $integrationId
     * @param int|null|false $expected
     * @param bool           $optional
     */
    public function testGetChannel($integrationId, $expected, $optional = false)
    {
        $integration = $this->createMock('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $integration->expects($this->any())
            ->method('getId')->will($this->returnValue($integrationId));

        if (false === $expected) {
            $this->expectException('\LogicException');
            $this->expectExceptionMessage('Unable to find channel for given integration');
        }

        $existingIntegrationId = self::TEST_INTEGRATION_ID_WITH_CHANNEL;
        $existingChannelId     = self::TEST_CHANNEL_ID;
        $this->getDriverConnectionMock($this->em)->expects($this->atLeastOnce())
            ->method('query')
            ->will(
                $this->returnCallback(
                    function () use ($integrationId, $expected, $existingIntegrationId, $existingChannelId) {
                        return $this->createFetchStatementMock(
                            [['id_0' => $existingChannelId, 'id_1' => $existingIntegrationId]]
                        );
                    }
                )
            );

        $result1 = $this->helper->getChannel($integration, $optional);
        $result2 = $this->helper->getChannel($integration, $optional);
        $this->assertSame(is_object($result1) ? $result1->getId() : $result1, $expected);
        $this->assertSame($result1, $result2, 'Ensure query executed once');
    }

    /**
     * @return array
     */
    public function getChannelDataProvider()
    {
        return [
            'should return channel'                              => [
                '$integrationId' => self::TEST_INTEGRATION_ID_WITH_CHANNEL,
                '$expected'      => self::TEST_CHANNEL_ID,
            ],
            'should return null, because optional and not found' => [
                '$integrationId' => self::TEST_INTEGRATION_ID_WITHOUT_CHANNEL,
                '$expected'      => null,
                '$optional'      => true
            ],
            'should throw exception, channel not found'          => [
                '$integrationId' => self::TEST_INTEGRATION_ID_WITHOUT_CHANNEL,
                '$expected'      => false,
            ],
        ];
    }
}
