<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Provider;

use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\CaseBundle\Form\Type\CaseMailboxProcessSettingsType;
use Oro\Bundle\CaseBundle\Provider\CaseMailboxProcessProvider;

class CaseMailboxProcessProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CaseMailboxProcessProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new CaseMailboxProcessProvider();
    }

    public function testIsEnabled()
    {
        self::assertTrue($this->provider->isEnabled());
    }

    public function testGetProcessDefinitionName()
    {
        self::assertEquals(
            CaseMailboxProcessProvider::PROCESS_DEFINITION_NAME,
            $this->provider->getProcessDefinitionName()
        );
    }

    public function testGetSettingsEntityFQCN()
    {
        self::assertEquals(CaseMailboxProcessSettings::class, $this->provider->getSettingsEntityFQCN());
    }

    public function testGetSettingsFormType()
    {
        self::assertEquals(CaseMailboxProcessSettingsType::class, $this->provider->getSettingsFormType());
    }

    public function testGetLabel()
    {
        self::assertEquals('oro.case.mailbox.process.case.label', $this->provider->getLabel());
    }
}
