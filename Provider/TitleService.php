<?php

namespace Oro\Bundle\NavigationBundle\Provider;

use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Routing\Route;
use JMS\Serializer\Serializer;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\NavigationBundle\Entity\Title;
use Oro\Bundle\NavigationBundle\Title\TitleReader\ConfigReader;
use Oro\Bundle\NavigationBundle\Title\TitleReader\AnnotationsReader;
use Oro\Bundle\NavigationBundle\Title\StoredTitle;

class TitleService implements TitleServiceInterface
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
    private $params = array();

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
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var Serializer
     */
    protected $serializer = null;

    public function __construct(
        AnnotationsReader $reader,
        ConfigReader $configReader,
        Translator $translator,
        ObjectManager $em,
        Serializer $serializer
    ) {
        $this->readers = array($reader, $configReader);

        $this->translator = $translator;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * Return rendered translated title
     *
     * @param array $params
     * @param null $title
     * @param null $prefix
     * @param null $suffix
     * @return $this
     */
    public function render($params = array(), $title = null, $prefix = null, $suffix = null)
    {
        $title = is_null($title) ? $this->getTemplate() : $title;
        $prefix = is_null($prefix) ? $this->prefix : $prefix;
        $suffix = is_null($suffix) ? $this->suffix : $suffix;
        $params = empty($params) ? $this->getParams() : $params;

        $trans = $this->translator;

        $translatedTemplate = $trans->trans($title, $params);

        if (!is_null($suffix)) {
            $suffix = $trans->trans($suffix, $params);
        }

        if (!is_null($prefix)) {
            $prefix = $trans->trans($prefix, $params);
        }

        $translatedTemplate = $prefix . $translatedTemplate . $suffix;

        return $translatedTemplate;
    }

    /**
     * Set properties from array
     *
     * @param array $values
     * @return $this
     */
    public function setData(array $values)
    {
        if (isset($values['titleTemplate']) && $this->getTemplate() == null) {
            $this->setTemplate($values['titleTemplate']);
        }
        if (isset($values['params'])) {
            $this->setParams($values['params']);
        }
        if (isset($values['prefix'])) {
            $this->setPrefix($values['prefix']);
        }
        if (isset($values['suffix'])) {
            $this->setSuffix($values['suffix']);
        }

        return $this;
    }

    /**
     * Render serialized title
     *
     * @param string $titleData
     * @return string
     */
    public function renderStored($titleData)
    {
        /** @var $data \Oro\Bundle\NavigationBundle\Title\StoredTitle */
        $data =  $this->serializer->deserialize($titleData, 'Oro\Bundle\NavigationBundle\Title\StoredTitle', 'json');

        return $this->render($data->getParams(), $data->getTemplate(), $data->getPrefix(), $data->getSuffix());
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
     * Setter for params
     *
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Load title template from database
     *
     * @param string $route
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

    /**
     * Return serialized title data
     *
     * @return string
     */
    public function getSerialized()
    {
        $storedTitle = new StoredTitle();
        $storedTitle
            ->setTemplate($this->getTemplate())
            ->setParams($this->getParams())
            ->setPrefix($this->prefix)
            ->setSuffix($this->suffix);

        return $this->serializer->serialize($storedTitle, 'json');
    }
}
