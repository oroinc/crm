<?php

namespace Oro\Bundle\NavigationBundle\Title;

use Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Extractor\ExtractorInterface;

class TranslationExtractor implements ExtractorInterface
{
    /**
     * @var \Oro\Bundle\NavigationBundle\Provider\TitleService
     */
    private $titleService;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param \Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface $titleService
     */
    public function __construct(TitleServiceInterface $titleService)
    {
        $this->titleService    = $titleService;
    }

    /**
     * Extract titles for translation
     *
     * @param string $directory
     * @param \Symfony\Component\Translation\MessageCatalogue $catalogue
     *
     * @return MessageCatalogue
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        $titles = $this->titleService->getNotEmptyTitles();
        foreach ($titles as $titleRecord) {
            $message = $titleRecord['title'];
            $catalogue->set($message, $this->prefix . $message);
        }

        return $catalogue;
    }

    /**
     * Set prefix for translated strings
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
}
