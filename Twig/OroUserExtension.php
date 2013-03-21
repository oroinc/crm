<?php

namespace Oro\Bundle\UserBundle\Twig;

use Oro\Bundle\UserBundle\Acl\ManagerInterface;

class OroUserExtension extends \Twig_Extension
{
    /**
     * @var \Oro\Bundle\UserBundle\Acl\ManagerInterface
     */
    private $manager;

    public function __construct(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'resource_is_grant'      => new \Twig_Filter_Method($this, 'checkResourceIsGrant'),
        );
    }

    /**
     * Check if ACL resource is grant for current user
     *
     * @param string $aclId ACL Resource id
     *
     * @return bool
     */
    public function checkResourceIsGrant($aclId)
    {
        return $this->manager->isResourceGranted($aclId);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'user_extension';
    }
}