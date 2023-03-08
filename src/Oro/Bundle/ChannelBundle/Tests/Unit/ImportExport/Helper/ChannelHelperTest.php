<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\ImportExport\Helper;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\Testing\Unit\ORM\OrmTestCase;

class ChannelHelperTest extends OrmTestCase
{
    private const TEST_INTEGRATION_ID_WITH_CHANNEL = 1;
    private const TEST_INTEGRATION_ID_WITHOUT_CHANNEL = 2;
    private const TEST_CHANNEL_ID = 2;

    private EntityManagerInterface $em;
    private ChannelHelper $helper;

    protected function setUp(): void
    {
        $this->em = $this->getTestEntityManager();
        $this->em->getConfiguration()->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getManager')
            ->willReturn($this->em);

        $this->helper = new ChannelHelper($doctrine);
    }

    /**
     * @dataProvider getChannelDataProvider
     */
    public function testGetChannel(int $integrationId, int|false|null $expected, bool $optional)
    {
        $integration = $this->createMock(Channel::class);
        $integration->expects($this->any())
            ->method('getId')
            ->willReturn($integrationId);

        if (false === $expected) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Unable to find channel for given integration');
        }

        $existingIntegrationId = self::TEST_INTEGRATION_ID_WITH_CHANNEL;
        $existingChannelId     = self::TEST_CHANNEL_ID;
        $this->getDriverConnectionMock($this->em)->expects($this->atLeastOnce())
            ->method('query')
            ->willReturnCallback(function () use ($existingIntegrationId, $existingChannelId) {
                return $this->createFetchStatementMock(
                    [['id_0' => $existingChannelId, 'id_1' => $existingIntegrationId]]
                );
            });

        $result1 = $this->helper->getChannel($integration, $optional);
        $result2 = $this->helper->getChannel($integration, $optional);
        $this->assertSame($expected, $result1?->getId());
        $this->assertSame($result1, $result2, 'Ensure query executed once');
    }

    public function getChannelDataProvider(): array
    {
        return [
            'should return channel'                              => [
                'integrationId' => self::TEST_INTEGRATION_ID_WITH_CHANNEL,
                'expected'      => self::TEST_CHANNEL_ID,
                'optional'      => false
            ],
            'should return null, because optional and not found' => [
                'integrationId' => self::TEST_INTEGRATION_ID_WITHOUT_CHANNEL,
                'expected'      => null,
                'optional'      => true
            ],
            'should throw exception, channel not found'          => [
                'integrationId' => self::TEST_INTEGRATION_ID_WITHOUT_CHANNEL,
                'expected'      => false,
                'optional'      => false
            ],
        ];
    }
}
