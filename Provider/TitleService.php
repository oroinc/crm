<?php

namespace Oro\Bundle\NavigationBundle\Provider;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\NavigationBundle\Entity\Title;
use Oro\Bundle\NavigationBundle\Title\TitleReader\ConfigReader;
use Oro\Bundle\NavigationBundle\Title\TitleReader\AnnotationsReader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Routing\Route;

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

    /**
     * Current title template params
     *
     * @var array
     */
    private $params;

    /**
     * Current title suffix
     *
     * @var array
     */
    private $suffix = null;

    /**
     * Current title prefix
     *
     * @var array
     */
    private $prefix = null;

    /**
     * @var string
     */
    private $translatedTemplate;

    /**
     * @var \Twig_Environment
     */
    private $templateEngine;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(AnnotationsReader $reader, ConfigReader $configReader, \Twig_Environment $templateEngine, Translator $translator, ObjectManager $em)
    {
        $this->readers = array($reader, $configReader);

        $this->templateEngine = $templateEngine;
        $this->translator = $translator;
        $this->em = $em;
    }

    /**
     * Set template string
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Generate translated title
     *
     * @param array $params
     */
    public function generate($params)
    {
        $this->params = $params;
        $trans = $this->translator;

        $this->translatedTemplate = $trans->trans($this->template, $params);

        $suffix = '';
        if (!is_null($this->suffix)) {
            $suffix = $trans->trans($this->suffix, $params);
        }

        $prefix = '';
        if (!is_null($this->prefix)) {
            $prefix = $trans->trans($this->prefix, $params);
        }

        $this->translatedTemplate = $prefix . $this->translatedTemplate . $suffix;
    }

    /**
     * Set string suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Set string prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Return params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Return rendered translated title
     *
     * @return string
     */
    public function render()
    {
        return $this->templateEngine->render($this->translatedTemplate, $this->getParams());
    }


    /**
     * Updates title index
     *
     * @param array $routes
     */
    public function update($routes)
    {
        $data = $routes;

        foreach ($this->readers as $reader) {
            $data = array_merge($data, $reader->getData());
        }

        $entity = new Title();
        $bdData = $this->em->getRepository(get_class($entity))->getExistItems();

        $existInDB = array_intersect_key($data, $bdData);

        foreach ($data as $route => $title) {
            if (!array_key_exists($route, $existInDB)) {
                $entity = new Title();
                $entity->setTitle($title instanceof Route ? '' : $title);
                $entity->setRoute($route);

                $this->em->persist($entity);
            }
        }

        foreach ($bdData as $route => $title) {
            if (!array_key_exists($route, $existInDB)) {
                $this->em->remove($entity);
            }
        }

        $this->em->flush();
    }
}
