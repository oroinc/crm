<?php
namespace Oro\Bundle\NavigationBundle\Title\TitleReader;

use Symfony\Component\Yaml\Yaml;

class ConfigReader extends Reader
{
    /**
     * Get Route/Title information from bundle configs
     *
     * @return array()
     */
    public function getData()
    {
        $titleConfig = array();

        $dirs = $this->getScanDirectories();
        foreach ($dirs as $dir) {
            if (is_file($file = $dir . '/Resources/config/titles.yml')) {
                $titleConfig += Yaml::parse(realpath($file));
            }
        }

        return $titleConfig;
    }
}
