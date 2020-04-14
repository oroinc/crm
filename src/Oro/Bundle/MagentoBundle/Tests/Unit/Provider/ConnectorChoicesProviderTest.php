<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Provider\ConnectorChoicesProvider;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConnectorChoicesProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var  ConnectorChoicesProvider */
    protected $connectorChoicesProvider;

    /** @var  TypesRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $typesRegistry;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $translator;

    protected function setUp(): void
    {
        $this->typesRegistry = $this->createMock(TypesRegistry::class);
        $this->translator    = $this->createMock(Translator::class);

        $this->translator
             ->expects($this->any())
             ->method('trans')
             ->will($this->returnArgument(0));

        $this->connectorChoicesProvider = new ConnectorChoicesProvider(
            $this->typesRegistry,
            $this->translator
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset(
            $this->typesRegistry,
            $this->translator,
            $this->connectorChoicesProvider
        );
    }

    public function testGetAllowedConnectorsChoices()
    {
        $expected = ['test' => 'test'];

        $this->typesRegistry
             ->expects($this->once())
             ->method('getAvailableConnectorsTypesChoiceList')
             ->willReturn($expected);

        $choices = $this->connectorChoicesProvider->getAllowedConnectorsChoices(true, true, 'magento');
        $this->assertEquals(
            $expected,
            $choices
        );
    }
}
