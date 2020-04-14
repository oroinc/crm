<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MagentoBundle\Acl\Voter\OrganizationAnnotationVoter;
use Oro\Bundle\MagentoBundle\Model\ChannelSettingsProvider;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OrganizationAnnotationVoterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ChannelSettingsProvider
     */
    private $settingsProvider;

    /**
     * @var OrganizationAnnotationVoter
     */
    private $voter;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->settingsProvider = $this->createMock(ChannelSettingsProvider::class);
        $this->voter = new OrganizationAnnotationVoter($this->doctrineHelper);
        $this->voter->setSettingsProvider($this->settingsProvider);
    }

    protected function tearDown(): void
    {
        unset($this->voter, $this->doctrineHelper);
    }

    /**
     * @param object $object
     * @param string $className
     * @param array $attributes
     * @param bool $hasOrganizationApplicableChannels
     * @param bool $expected
     *
     * @dataProvider attributesDataProvider
     */
    public function testVote(
        $object,
        $className,
        $attributes,
        $hasOrganizationApplicableChannels,
        $expected
    ) {
        $this->voter->setClassName($className);

        $this->settingsProvider->expects($this->any())
            ->method('hasOrganizationApplicableChannels')
            ->will($this->returnValue($hasOrganizationApplicableChannels));

        /** @var TokenInterface $token */
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->assertEquals(
            $expected,
            $this->voter->vote($token, $object, $attributes)
        );
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function attributesDataProvider()
    {
        $className = 'Oro\Bundle\OrganizationBundle\Entity\Organization';

        return [
            // default permissions not supported
            [new Organization(), $className, ['CREATE'], false, OrganizationAnnotationVoter::ACCESS_ABSTAIN],
            [new Organization(), $className, ['VIEW'], true, OrganizationAnnotationVoter::ACCESS_ABSTAIN],
            // default permissions with matched channels
            [
                new Organization(),
                $className,
                ['oro_magento_customer_create'],
                true,
                OrganizationAnnotationVoter::ACCESS_GRANTED,
            ],
            [
                new Organization(),
                $className,
                ['oro_magento_customer_update'],
                true,
                OrganizationAnnotationVoter::ACCESS_ABSTAIN,
            ],
            // default permissions without matched channels
            [
                new Organization(),
                $className,
                ['oro_magento_customer_create'],
                false,
                OrganizationAnnotationVoter::ACCESS_DENIED,
            ],
            [
                new Organization(),
                $className,
                ['oro_magento_customer_update'],
                false,
                OrganizationAnnotationVoter::ACCESS_ABSTAIN,
            ],
        ];
    }
}
