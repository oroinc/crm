<?php

namespace Oro\Bundle\WindowsBundle\Twig;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\WindowsBundle\Entity\WindowsState;

class WindowsExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'oro_windows';

    /**
     * @var Twig_Environment $environment
     */
    protected $environment;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param Twig_Environment $environment
     * @param SecurityContextInterface $securityContext
     * @param EntityManager $em
     */
    public function __construct(
        Twig_Environment $environment,
        SecurityContextInterface $securityContext,
        EntityManager $em
    ) {
        $this->environment = $environment;
        $this->securityContext = $securityContext;
        $this->em = $em;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_windows_restore' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html')))
        );
    }

    /**
     * Renders a menu with the specified renderer.
     *
     * @param array $options
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function render(array $options = array())
    {
        if (!($user = $this->getUser())) {
            return '';
        }

        $states = array();
        $windowsList = $this->em->getRepository('OroWindowsBundle:WindowsState')->findBy(array('user' => $user));
        /** @var $windowState WindowsState */
        foreach ($windowsList as $windowState) {
            $data = $windowState->getData();
            if (!$data) {
                $this->em->remove($windowState);
                $this->em->flush();
            } else {
                $states[$windowState->getId()] = $data;
            }
        }

        return $this->environment->render(
            "OroWindowsBundle::states.html.twig",
            array("states" => $states)
        );
    }

    /**
     * Get a user from the Security Context
     *
     * @return null|mixed
     * @throws \LogicException If SecurityBundle is not available
     * @see Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    public function getUser()
    {
        /** @var $token TokenInterface */
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
