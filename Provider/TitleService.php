<?php

namespace Oro\Bundle\NavigationBundle\Provider;

use Symfony\Component\Yaml\Yaml;

class TitleService
{
    private $template;
    private $templateEngine;
    private $bundles;

    public function __construct($bundles)
    {
        //$this->templateEngine = $templateEngine;
        $this->bundles = $bundles;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function generate($params)
    {
        $placeholders = '';
        return str_replace($placeholders, $params, $this->template);
    }

    public function setSuffix($suffix)
    {

    }

    public function setPrefix($prefix)
    {

    }

    public function getParams()
    {

    }

    /**
     * Get titles array from config files
     *
     * @return array
     */
    public function getTitlesConfig()
    {
        $titleConfig = array();
        foreach ($this->bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/titles.yml')) {
                $titleConfig += Yaml::parse(realpath($file));
            }
        }

        return $titleConfig;
    }
}
