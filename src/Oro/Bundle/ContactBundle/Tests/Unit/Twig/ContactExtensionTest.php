<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Twig;

use Oro\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use Oro\Bundle\ContactBundle\Model\Social;
use Oro\Bundle\ContactBundle\Twig\ContactExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContactExtensionTest extends TestCase
{
    use TwigExtensionTestCaseTrait;

    private SocialUrlFormatter&MockObject $urlFormatter;
    private ContactExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlFormatter = $this->createMock(SocialUrlFormatter::class);

        $container = self::getContainerBuilder()
            ->add(SocialUrlFormatter::class, $this->urlFormatter)
            ->getContainer($this);

        $this->extension = new ContactExtension($container);
    }

    /**
     * @dataProvider socialUrlDataProvider
     */
    public function testGetSocialUrl(string $expectedUrl, ?string $socialType, ?string $username): void
    {
        if ($socialType && $username) {
            $this->urlFormatter->expects(self::once())
                ->method('getSocialUrl')
                ->with($socialType, $username)
                ->willReturnCallback(function ($socialType, $username) {
                    return 'http://' . $socialType . '/' . $username;
                });
        } else {
            $this->urlFormatter->expects(self::never())
                ->method('getSocialUrl');
        }

        self::assertEquals(
            $expectedUrl,
            self::callTwigFunction($this->extension, 'oro_social_url', [$socialType, $username])
        );
    }

    public function socialUrlDataProvider(): array
    {
        return [
            'no type' => [
                'expectedUrl' => '#',
                'socialType'  => null,
                'username'    => 'me',
            ],
            'no username' => [
                'expectedUrl' => '#',
                'socialType'  => Social::TWITTER,
                'username'    => null,
            ],
            'valid data' => [
                'expectedUrl' => 'http://' . Social::TWITTER . '/me',
                'socialType'  => Social::TWITTER,
                'username'    => 'me',
            ],
        ];
    }
}
