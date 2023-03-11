<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ContactBundle\DependencyInjection\OroContactExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroContactExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroContactExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertEquals(
            [
                'twitter' => 'https://twitter.com/%%username%%',
                'facebook' => 'https://www.facebook.com/%%username%%',
                'google_plus' => 'https://profiles.google.com/%%username%%',
                'linked_in' => 'http://www.linkedin.com/in/%%username%%'
            ],
            $container->getParameter('oro_contact.social_url_format')
        );
    }
}
