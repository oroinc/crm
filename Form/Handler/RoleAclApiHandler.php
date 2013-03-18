<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\UserBundle\Acl\Manager;

use Oro\Bundle\UserBundle\Entity\Role;

class RoleAclApiHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Manager
     */
    protected $aclManager;

    /**
     *
     * @param FormInterface $form
     * @param Request       $request
     * @param Manager $aclManager
     */
    public function __construct(FormInterface $form, Request $request, Manager $aclManager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->aclManager = $aclManager;
    }

    /**
     * Process form
     *
     * @param  Role $entity
     * @return bool True on successfull processing, false otherwise
     */
    public function process(Role $entity)
    {
        if (in_array($this->request->getMethod(), array('POST'))) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->addAclResources($entity, $this->form->getData());

                return true;
            }
        }

        if (in_array($this->request->getMethod(), array('DELETE'))) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->deleteAclResources($entity, $this->form->getData());

                return true;
            }
        }

        return false;
    }

    private function addAclResources($entity, array $data)
    {

    }

    private function deleteAclResources($entity, array $data)
    {

    }
}
