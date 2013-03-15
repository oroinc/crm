<?php

namespace Oro\Bundle\GridBundle\Action;

use Oro\Bundle\UserBundle\Acl\ManagerInterface;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $aclResource = null;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var ActionUrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var ManagerInterface
     */
    protected $aclManager;

    /**
     * @var bool
     */
    protected $isProcessed = false;

    /**
     * @param ActionUrlGeneratorInterface $urlGenerator
     * @param ManagerInterface $aclManager
     */
    public function __construct(ActionUrlGeneratorInterface $urlGenerator, ManagerInterface $aclManager)
    {
        $this->urlGenerator = $urlGenerator;
        $this->aclManager   = $aclManager;
    }

    /**
     * Filter name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Action type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * ACL resource name
     *
     * @return string|null
     */
    public function getAclResource()
    {
        return $this->aclResource;
    }

    /**
     * Action options (route, ACL resource etc.)
     *
     * @return array
     */
    public function getOptions()
    {
        if (!$this->isProcessed) {
            $this->processRouteOptions();
        }

        return $this->options;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $aclResource
     */
    public function setAclResource($aclResource)
    {
        $this->aclResource = $aclResource;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param string $optionName
     * @throws \LogicException
     */
    protected function assertOption($optionName)
    {
        if (!isset($this->options[$optionName])) {
            throw new \LogicException(
                'There is no option "' . $optionName . '" for action "' . $this->name . '".'
            );
        }
    }

    /**
     * Process route options ("route", "parameters", "placeholders")
     *
     * @throws \LogicException
     */
    protected function processRouteOptions()
    {
        $this->assertOption('route');

        $routeName = $this->options['route'];

        if (isset($this->options['parameters'])) {
            $parameters = $this->options['parameters'];
            unset($this->options['parameters']);
        } else {
            $parameters = array();
        }

        if (!isset($this->options['placeholders'])) {
            $this->options['placeholders'] = array();
        }
        $placeholders = $this->options['placeholders'];

        // generate correct url
        $this->options['url'] = $this->urlGenerator->generate($routeName, $parameters, $placeholders);
        unset($this->options['route']);

        foreach ($this->options['placeholders'] as $key => $value) {
            unset($this->options['placeholders'][$key]);
            $key = '{' . $key .'}';
            $this->options['placeholders'][$key] = $value;
        }
    }

    /**
     * Check whether action allowed for current user
     *
     * @return bool
     */
    public function isGranted()
    {
        if ($this->aclResource) {
            return $this->aclManager->isResourceGranted($this->aclResource);
        }

        return true;
    }
}
