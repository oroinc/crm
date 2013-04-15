<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Title;

use Oro\Bundle\NavigationBundle\Title\TranslationExtractor;

class TranslationExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TranslationExtractor
     */
    private $extractor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $titleService;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->titleService = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Provider\TitleService')
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->extractor = new TranslationExtractor($this->titleService);
    }

    /**
     * Test message extract
     */
    public function testExtract()
    {
        $messageCatalogue = $this->getMockBuilder('Symfony\Component\Translation\MessageCatalogue')
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->titleService->expects($this->once())
                           ->method('getNotEmptyTitles')
                           ->will($this->returnValue(array('title' => 'Test title')));

        $messageCatalogue->expects($this->once())
                         ->method('set');

        $this->extractor->setPrefix('__');
        $this->extractor->extract('', $messageCatalogue);
    }
}
