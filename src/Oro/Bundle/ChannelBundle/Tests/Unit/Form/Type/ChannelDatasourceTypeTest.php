<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\ChannelBundle\Form\Extension\IntegrationTypeExtension;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelDatasourceType;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider as ChannelSettingsProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Oro\Bundle\FormBundle\Autocomplete\SearchRegistry;
use Oro\Bundle\FormBundle\Form\Extension\TooltipFormExtension;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Form\EventListener\ChannelFormSubscriber;
use Oro\Bundle\IntegrationBundle\Form\EventListener\DefaultOwnerSubscriber;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Oro\Bundle\IntegrationBundle\Form\Type\IntegrationTypeSelectType;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\SettingsProvider;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\TranslationBundle\Translation\IdentityTranslator;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use Oro\Bundle\UserBundle\Form\Type\OrganizationUserAclSelectType;
use Oro\Bundle\UserBundle\Form\Type\UserAclSelectType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Oro\Component\Testing\Unit\TestContainerBuilder;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Csrf\Type\FormTypeCsrfExtension;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Context\ExecutionContextFactory;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\LoaderChain;
use Symfony\Component\Validator\Validator\RecursiveValidator;

class ChannelDatasourceTypeTest extends FormIntegrationTestCase
{
    private const TEST_ID = 1;
    private const TEST_NAME = 'name';
    private const TEST_TYPE = 'type';
    private const TEST_ID_FIELD_NAME = 'id';
    private const TEST_SUBMITTED_NAME = 'nameSubmitted';
    private const TEST_CHANNEL_TYPE = 'channelType';

    /** @var ChannelDatasourceType */
    private $type;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var string */
    private $testEntityName = 'OroIntegration:Channel';

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $entityConfigProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    protected function setUp(): void
    {
        $this->entityConfigProvider = $this->createMock(ConfigProvider::class);
        $this->translator = $this->createMock(Translator::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        $this->type = new ChannelDatasourceType($this->registry, $this->testEntityName);
        parent::setUp();
    }

    protected function getExtensions()
    {
        $transportName = uniqid('transport');
        $assetsHelper = $this->createMock(Packages::class);
        $integrationType = $this->createMock(ChannelInterface::class);
        $transportType = $this->createMock(TransportInterface::class);
        $transportType->expects($this->exactly(2))
            ->method('getLabel')
            ->willReturn($transportName);

        $registry = new TypesRegistry();
        $registry->addChannelType(self::TEST_TYPE, $integrationType);
        $registry->addTransportType($transportName, self::TEST_TYPE, $transportType);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $em = $this->createMock(EntityManager::class);
        $metadata = $this->createMock(ClassMetadata::class);
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('OroUser:User')
            ->willReturn($metadata);
        $metadata->expects($this->once())
            ->method('getSingleIdentifierFieldName')
            ->willReturn(self::TEST_ID_FIELD_NAME);
        $searchHandler = $this->createMock(SearchHandlerInterface::class);
        $searchHandler->expects($this->any())
            ->method('getEntityName')
            ->willReturn('OroUser:User');
        $searchRegistry = new SearchRegistry(
            TestContainerBuilder::create()->add('acl_users', $searchHandler)->getContainer($this)
        );

        $config = $this->createMock(ConfigInterface::class);
        $config->expects($this->any())
            ->method('has')
            ->with('grid_name')
            ->willReturn(true);
        $config->expects($this->any())
            ->method('get')
            ->with('grid_name')
            ->willReturn('test_grid');
        $cp = $this->createMock(ConfigProvider::class);
        $cp->expects($this->any())
            ->method('getConfig')
            ->willReturn($config);
        $cm = $this->createMock(ConfigManager::class);
        $cm->expects($this->any())
            ->method('getProvider')
            ->willReturn($cp);

        $validator = new RecursiveValidator(
            new ExecutionContextFactory(new IdentityTranslator()),
            new LazyLoadingMetadataFactory(new LoaderChain([])),
            new ConstraintValidatorFactory()
        );

        $settingsProvider = $this->createMock(ChannelSettingsProvider::class);

        return [
            new PreloadedExtension(
                [
                    $this->type,
                    ChannelType::class                       => $this->getChannelType($registry),
                    IntegrationTypeSelectType::class         => new IntegrationTypeSelectType($registry, $assetsHelper),
                    OrganizationUserAclSelectType::class     => new OrganizationUserAclSelectType(),
                    UserAclSelectType::class                 => new UserAclSelectType(),
                    OroEntitySelectOrCreateInlineType::class => new OroEntitySelectOrCreateInlineType(
                        $authorizationChecker,
                        $cm,
                        $em,
                        $searchRegistry
                    ),
                    OroJquerySelect2HiddenType::class        => new OroJquerySelect2HiddenType(
                        $em,
                        $searchRegistry,
                        $cp
                    )
                ],
                [
                    FormType::class    => [
                        new FormTypeCsrfExtension($this->createMock(CsrfTokenManagerInterface::class)),
                        new FormTypeValidatorExtension($validator),
                        new TooltipFormExtension($this->entityConfigProvider, $this->translator),
                    ],
                    ChannelType::class => [
                        new IntegrationTypeExtension($settingsProvider)
                    ]
                ]
            )
        ];
    }

    public function testFormSubmit()
    {
        $this->prepareEmMock();

        $form = $this->factory->create(
            ChannelDatasourceType::class,
            null,
            [
                'type'            => self::TEST_CHANNEL_TYPE,
                'csrf_protection' => false
            ]
        );
        $form->submit(
            [
                'identifier' => self::TEST_ID,
                'data'       => json_encode(['name' => self::TEST_SUBMITTED_NAME, 'type' => self::TEST_TYPE])
            ]
        );

        /** @var Integration $integration */
        $integration = $form->getData();
        $viewData = $form->getViewData();

        $this->assertSame(self::TEST_TYPE, $integration->getType());
        $this->assertSame(self::TEST_SUBMITTED_NAME, $integration->getName());

        $this->assertSame(
            [
                'type'       => self::TEST_TYPE,
                'data'       => null,
                'identifier' => $integration,
                'name'       => self::TEST_SUBMITTED_NAME
            ],
            $viewData
        );
    }

    private function prepareEmMock()
    {
        $em = $this->createMock(EntityManager::class);
        $metadata = $this->createMock(ClassMetadata::class);
        $repo = $this->createMock(EntityRepository::class);
        $entity = new Integration();
        $entity->setName(self::TEST_NAME);
        $entity->setType(self::TEST_TYPE);

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($this->testEntityName)
            ->willReturn($em);
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->testEntityName)
            ->willReturn($metadata);
        $metadata->expects($this->once())
            ->method('getSingleIdentifierFieldName')
            ->willReturn(self::TEST_ID_FIELD_NAME);
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->testEntityName)
            ->willReturn($repo);
        $repo->expects($this->once())
            ->method('find')
            ->with(self::TEST_ID)
            ->willReturn($entity);
    }

    private function getChannelType(TypesRegistry $registry): ChannelType
    {
        $settingsProvider = $this->createMock(SettingsProvider::class);
        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $channelSubscriber = new ChannelFormSubscriber(
            $registry,
            $settingsProvider,
            (new InflectorFactory())->build()
        );
        $ownerSubscriber = $this->getMockBuilder(DefaultOwnerSubscriber::class)
            ->setConstructorArgs([$tokenAccessor, $registry])
            ->onlyMethods(['postSet'])
            ->getMock();

        return new ChannelType(
            $ownerSubscriber,
            $channelSubscriber
        );
    }
}
