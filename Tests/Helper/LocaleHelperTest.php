<?php
namespace Oro\Bundle\FlexibleEntityBundle\Test\Entity;

use Symfony\Component\DependencyInjection\Container;

use Oro\Bundle\FlexibleEntityBundle\Helper\LocaleHelper;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class LocaleHelperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @staticvar string
     */
    protected static $localeCode = 'fr';

    /**
     * @staticvar string
     */
    protected static $localeCodeConfig = 'en';

    /**
     * test related method
     */
    public function testGetDefaultLocaleCode()
    {
        $container = new Container();

        $locale = new LocaleHelper(self::$localeCode, $container);

        $this->assertEquals(self::$localeCode, $locale->getDefaultLocaleCode());
    }

    /**
     * Test related method
     */
    public function testGetCurrentLocaleCode()
    {
        $container = new Container();

        $locale = new LocaleHelper(self::$localeCode, $container);

        $this->assertEquals(self::$localeCode, $locale->getCurrentLocaleCode());
    }
}
