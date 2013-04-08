<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\Form;

class BaseController extends ContainerAware
{
    /**
     * Shortcut to get entity
     *
     * @param  string     $name Repository name
     * @param  int        $id   Entity id
     * @return mixed      Entity object
     * @throws \SoapFault
     */
    protected function getEntity($repo, $id)
    {
        $entity = $this->getManager()->find($repo, (int) $id);

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('Record #%u can not be found', $id));
        }

        return $entity;
    }

    /**
     * Form processing
     *
     * @param  string     $form   Form name to process
     * @param  mixed      $entity Entity object
     * @return bool       True on success
     * @throws \SoapFault
     */
    protected function processForm($form, $entity)
    {
        if (!$this->container->get(sprintf('oro_user.form.handler.%s.api', $form))->process($entity)) {
            throw new \SoapFault('BAD_REQUEST', $this->getFormErrors($this->container->get(sprintf('oro_user.form.%s.api', $form))));
        }

        return true;
    }

    /**
     * @param Form $form
     * @return string All error messages concatinated into one string
     */
    protected function getFormErrors(Form $form)
    {
        $errors = '';

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

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }
}
