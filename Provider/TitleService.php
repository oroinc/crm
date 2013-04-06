<?php

namespace Oro\Bundle\NavigationBundle\Provider;

use Oro\Bundle\NavigationBundle\Title\TitleReader\ConfigReader;
use Oro\Bundle\NavigationBundle\Title\TitleReader\AnnotationsReader;

class TitleService
{
    /**
     * Title template
     *
     * @var string
     */
    private $template;

    /**
     * Title data readers
     *
     * @var array
     */
    private $readers = array();

    public function __construct(AnnotationsReader $reader, ConfigReader $configReader)
    {
        $this->readers = array($reader, $configReader);
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


    public function update($routes)
    {

        $data = array();

        foreach ($this->readers as $reader) {
            $data = array_merge_recursive($data, $reader->getData());
        }
    }
}
