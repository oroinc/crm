<?php

namespace Oro\Bundle\NavigationBundle\Title;

use Oro\Bundle\NavigationBundle\Provider\TitleService;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Extractor\ExtractorInterface;

class TranslationExtractor implements ExtractorInterface
{
    private $titleService;
    private $catalogue;
    private $domain;
    private $prefix;

    /**
     * @param \Oro\Bundle\NavigationBundle\Provider\TitleService $titleService
     */
    public function __construct(TitleService $titleService)
    {
        $this->titleService    = $titleService;
        $this->catalogue       = false;
        $this->domain          = false;
    }

    /**
     * @param string $directory
     * @param \Symfony\Component\Translation\MessageCatalogue $catalogue
     * @throws \RuntimeException
     * @throws \Exception
     * @return bool
     * @return \Symfony\Component\Translation\MessageCatalogue
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        if ($this->catalogue) {
            throw new \RuntimeException('Invalid state');
        }

        $locale = 'en';
        $this->catalogue = new MessageCatalogue($locale);

        $titles = $this->titleService->getNotEmptyTitles();
        foreach ($titles as $titleRecord) {
            $message = $titleRecord['title'];
            $catalogue->set($message, $this->prefix . $message);
        }

        $catalogue = $this->catalogue;
        $this->catalogue = false;

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
