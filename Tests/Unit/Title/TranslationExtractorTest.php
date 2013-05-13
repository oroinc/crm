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

        $repo = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Entity\Repository\TitleRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('getTitles')
            ->will($this->returnValue(array(array('title' => 'Test title'))));

        $this->titleService->expects($this->once())
            ->method('getStoredTitlesRepository')
            ->will($this->returnValue($repo));

        $messageCatalogue->expects($this->once())
            ->method('set');

        $this->extractor->setPrefix('__');
        $this->extractor->extract('', $messageCatalogue);
    }
}
