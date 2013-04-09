<?php

namespace Oro\Bundle\NavigationBundle\Provider;

use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Routing\Route;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\NavigationBundle\Entity\Title;
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

    public function __construct(
        AnnotationsReader $reader,
        ConfigReader $configReader,
        \Twig_Environment $templateEngine,
        Translator $translator,
        ObjectManager $em
    ) {
        $this->readers = array($reader, $configReader);

        $this->templateEngine = $templateEngine;
        $this->translator = $translator;
        $this->em = $em;
    }

    /**
     * Return rendered translated title
     *
     * @param array $params
     * @param null $title
     * @param bool $storeData
     * @return $this
     */
    public function render($params = array(), $title = null, $storeData = false)
    {
        if ($storeData) {
            $this->params = $params;
            $this->template = is_null($title) ? $this->template : $title;
        }

        $trans = $this->translator;

        $translatedTemplate = $trans->trans($this->template);

        $suffix = '';
        if (!is_null($this->suffix)) {
            $suffix = $trans->trans($this->suffix, $params);
        }

        $prefix = '';
        if (!is_null($this->prefix)) {
            $prefix = $trans->trans($this->prefix, $params);
        }

        $translatedTemplate = $prefix . $translatedTemplate . $suffix;

        return $this->templateEngine->render($translatedTemplate, $params);
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
     * Get template string
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
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
     * Load title template from database
     *
     * @param  string$route
     */
    public function loadByRoute($route)
    {
        /** @var $bdData Title */
        $bdData = $this->em->getRepository('Oro\Bundle\NavigationBundle\Entity\Title')->findOneBy(
            array('route' => $route)
        );

        if ($bdData) {
            $this->setTemplate($bdData->getTitle());
        }
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
            /** @var $reader  \Oro\Bundle\NavigationBundle\Title\TitleReader\Reader */
            $data = array_merge($data, $reader->getData($routes));
        }

        $bdData = $this->em->getRepository('Oro\Bundle\NavigationBundle\Entity\Title')->findAll();

        foreach ($bdData as $entity) {
            /** @var $entity Title */

            if (!array_key_exists($entity->getRoute(), $data)) {
                // remove not existing entries
                $this->em->remove($entity);

                continue;
            }

            $route = $entity->getRoute();
            $title = $data[$route] instanceof Route ? '' : $data[$route];

            // update existing system titles
            if ($entity->getIsSystem()) {
                $entity->setTitle($title);
                $this->em->persist($entity);
            }

            unset($data[$route]);
        }

        // create title items for new routes
        foreach ($data as $route => $title) {
            $entity = new Title();
            $entity->setTitle($title instanceof Route ? '' : $title);
            $entity->setRoute($route);
            $entity->setIsSystem(true);

            $this->em->persist($entity);
        }

        $this->em->flush();
    }
}
