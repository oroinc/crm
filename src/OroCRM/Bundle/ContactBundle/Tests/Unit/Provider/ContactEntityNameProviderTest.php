<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Formatter\ContactNameFormatter;
use OroCRM\Bundle\ContactBundle\Provider\ContactEntityNameProvider;

class ContactEntityNameProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContactEntityNameProvider */
    protected $provider;

    /** @var ServiceLink */
    protected $nameFormatterLink;

    /** @var ServiceLink */
    protected $dqlNameFormatterLink;

    /** @var ContactNameFormatter */
    protected $contactNameFormatter;

    /** @var DQLNameFormatter */
    protected $dqlNameFormatter;

    protected function setUp()
    {
        $this->contactNameFormatter = $this
            ->getMockBuilder('OroCRM\Bundle\ContactBundle\Formatter\ContactNameFormatter')
            ->disableOriginalConstructor()->getMock();

        $this->dqlNameFormatter = $this
            ->getMockBuilder('Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter')
            ->disableOriginalConstructor()->getMock();

        $this->nameFormatterLink = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()->getMock();

        $this->dqlNameFormatterLink = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()->getMock();

        $this->provider = new ContactEntityNameProvider($this->nameFormatterLink, $this->dqlNameFormatterLink);
    }

    /**
     * @dataProvider getNameDataProvider
     *
     * @param $format
     * @param $locale
     * @param $entity
     * @param $expected
     */
    public function testGetName($format, $locale, $entity, $expected)
    {
        if ($expected) {
            $this->contactNameFormatter->expects($this->once())->method('format')
                ->willReturn('Contact');

            $this->nameFormatterLink->expects($this->once())
                ->method('getService')
                ->willReturn($this->contactNameFormatter);
        }

        $result = $this->provider->getName($format, $locale, $entity);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getNameDQLDataProvider
     *
     * @param $format
     * @param $locale
     * @param $className
     * @param $alias
     * @param $expected
     */
    public function testGetNameDQL($format, $locale, $className, $alias, $expected)
    {
        if ($expected) {
            $this->dqlNameFormatter->expects($this->once())->method('getFormattedNameDQL')
                ->willReturn('Contact');

            $this->dqlNameFormatterLink->expects($this->once())
                ->method('getService')
                ->willReturn($this->dqlNameFormatter);
        }

        $result = $this->provider->getNameDQL($format, $locale, $className, $alias);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getNameDataProvider()
    {
        return [
            'test unsupported class' => [
                'format' => '',
                'locale' => null,
                'entity' => new \stdClass(),
                'expected' => false
            ],
            'test unsupported format' => [
                'format' => '',
                'locale' => null,
                'entity' => $this->getEntity(),
                'expected' => false
            ],
            'correct data' => [
                'format' => EntityNameProviderInterface::FULL,
                'locale' => '',
                'entity' => $this->getEntity(),
                'expected' => 'Contact'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getNameDQLDataProvider()
    {
        return [
            'test unsupported class Name' => [
                'format' => '',
                'locale' => null,
                'className' => '',
                'alias' => '',
                'expected' => false
            ],
            'test unsupported format' => [
                'format' => '',
                'locale' => null,
                'className' => ContactEntityNameProvider::CLASS_NAME,
                'alias' => '',
                'expected' => false
            ],
            'correct data' => [
                'format' => EntityNameProviderInterface::FULL,
                'locale' => null,
                'className' => ContactEntityNameProvider::CLASS_NAME,
                'alias' => '',
                'expected' => 'Contact'
            ]
        ];
    }

    /**
     * @return Contact
     */
    protected function getEntity()
    {
        return new Contact();
    }
}
