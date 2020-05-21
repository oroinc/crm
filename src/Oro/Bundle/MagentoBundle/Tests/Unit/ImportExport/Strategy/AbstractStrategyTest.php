<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\ChainEntityClassNameProvider;
use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\ImportExportBundle\Field\RelatedEntityStateHelper;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Strategy\Import\NewEntitiesHelper;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\AbstractImportStrategy;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class AbstractStrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ImportStrategyHelper
     */
    protected $strategyHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|StepExecution
     */
    protected $stepExecution;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|JobExecution
     */
    protected $jobExecution;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DatabaseHelper
     */
    protected $databaseHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DefaultOwnerHelper
     */
    protected $defaultOwnerHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ChannelHelper
     */
    protected $channelHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|AddressImportHelper
     */
    protected $addressHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ChainEntityClassNameProvider
     */
    protected $chainEntityClassNameProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TranslatorInterface
     */
    protected $translator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var NewEntitiesHelper
     */
    protected $newEntitiesHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject|RelatedEntityStateHelper */
    protected $relatedEntityStateHelper;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->fieldHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\Helper\FieldHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldHelper->expects($this->any())
            ->method('getIdentityValues')
            ->willReturn([]);

        $this->fieldHelper->expects($this->any())
            ->method('getFields')
            ->willReturn([]);

        $this->databaseHelper = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Field\DatabaseHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->strategyHelper = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->strategyHelper->expects($this->any())
            ->method('checkPermissionGrantedForEntity')
            ->will($this->returnValue(true));

        $this->defaultOwnerHelper = $this
            ->getMockBuilder('Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->channelHelper = $this
            ->getMockBuilder('Oro\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressHelper = $this
            ->getMockBuilder('Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->jobExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stepExecution->expects($this->any())->method('getJobExecution')
            ->will($this->returnValue($this->jobExecution));

        $this->chainEntityClassNameProvider = $this
            ->getMockBuilder('Oro\Bundle\EntityBundle\Provider\ChainEntityClassNameProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator = $this->getMockBuilder('Symfony\Contracts\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->relatedEntityStateHelper = $this->createMock(RelatedEntityStateHelper::class);

        $this->newEntitiesHelper = new NewEntitiesHelper();
        $this->logger = new NullLogger();
    }

    protected function tearDown(): void
    {
        unset(
            $this->eventDispatcher,
            $this->strategyHelper,
            $this->fieldHelper,
            $this->databaseHelper,
            $this->strategy,
            $this->stepExecution,
            $this->jobExecution,
            $this->defaultOwnerHelper,
            $this->logger,
            $this->channelHelper,
            $this->addressHelper,
            $this->doctrineHelper,
            $this->newEntitiesHelper
        );
    }

    /**
     * @return StrategyInterface|AbstractImportStrategy
     */
    abstract protected function getStrategy();
}
