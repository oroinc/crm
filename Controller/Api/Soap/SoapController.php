<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Soap;

use Oro\Bundle\SoapBundle\Controller\Api\ApiCrudInterface;
use Oro\Bundle\SoapBundle\Controller\Api\EntityManagerAwareInterface;
use Oro\Bundle\SoapBundle\Controller\Api\FormAwareInterface;
use Oro\Bundle\SoapBundle\Controller\Api\FormHandlerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

abstract class SoapController extends ContainerAware implements
     FormAwareInterface,
     FormHandlerAwareInterface,
     EntityManagerAwareInterface,
     ApiCrudInterface
{
    /**
     * {@inheritDoc}
     */
    public function handleGetListRequest($page = 1, $limit = 10)
    {
        return $this->getManager()->getList($limit, $page);
    }

    /**
     * {@inheritDoc}
     */
    public function handleGetRequest($id)
    {
        return $this->getEntity($id);
    }

    /**
     * {@inheritDoc}
     */
    public function handleUpdateRequest($id)
    {
        return $this->processForm($this->getEntity($id));
    }

    /**
     * {@inheritDoc}
     */
    public function handleCreateRequest()
    {
        return $this->processForm($this->getManager()->createEntity());
    }

    /**
     * {@inheritDoc}
     */
    public function handleDeleteRequest($id)
    {
        $entity = $this->getEntity($id);

        $em = $this->getManager()->getObjectManager();
        $em->remove($entity);
        $em->flush();

        return true;
    }

    /**
     * Get entity by identifier.
     *
     * @param mixed $id
     * @return object
     * @throws \SoapFault
     */
    protected function getEntity($id)
    {
        $entity = $this->getManager()->find($id);

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('Record #%u can not be found', $id));
        }

        return $entity;
    }

    /**
     * Form processing
     *
     * @param mixed $entity Entity object
     * @return bool True on success
     * @throws \SoapFault
     */
    protected function processForm($entity)
    {
        if (!$this->getFormHandler()->process($entity)) {
            throw new \SoapFault('BAD_REQUEST', $this->getFormErrors($this->getForm()));
        }

        return true;
    }

    /**
     * @param FormInterface $form
     * @return string All form's error messages concatenated into one string
     */
    protected function getFormErrors(FormInterface $form)
    {
        $errors = '';

        /** @var FormError $error */
        foreach ($form->getErrors() as $error) {
            $errors .= $error->getMessage() ."\n";
        }

        foreach ($form->all() as $key => $child) {
            if ($err = $this->getFormErrors($child)) {
                $errors .= sprintf("%s: %s\n", $key, $err);
            }
        }

        return $errors;
    }
}
