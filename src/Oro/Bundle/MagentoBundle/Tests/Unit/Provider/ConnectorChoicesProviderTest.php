<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\TranslationBundle\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Provider\ConnectorChoicesProvider;

class ConnectorChoicesProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ConnectorChoicesProvider */
    protected $connectorChoicesProvider;

    /** @var  TypesRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $typesRegistry;

    /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    public function setUp()
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

    public function tearDown()
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

        $this->assertEquals(
            $expected,
            $this->connectorChoicesProvider->getAllowedConnectorsChoices(true, true)
        );
    }
}
