<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\Form;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\AddressBundle\Entity\Address;

class AddressController extends ContainerAware
{
    /**
     * @Soap\Method("getAddresses")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Address[]")
     */
    public function cgetAction()
    {
        return $this->getManager()->getRepository('OroAddressBundle:Address')->findAll();
    }

    /**
     * @Soap\Method("getAddress")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Address")
     */
    public function getAction($id)
    {
        return $this->getEntity('OroAddressBundle:Address', (int)$id);
    }

    /**
     * @Soap\Method("createAddress")
     * @Soap\Param("address", phpType = "Oro\Bundle\AddressBundle\Entity\Address")
     * @Soap\Result(phpType = "boolean")
     */
    public function createAction($address)
    {
        $entity = $this->container->get('oro_address.address.manager')->createFlexible();
        $form = $this->container->get('oro_address.form.address.api')->getName();
        $this->container->get('oro_soap.request')->fix($form);
        return $this->processForm($entity);
    }

    /**
     * @Soap\Method("updateAddress")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("address", phpType = "Oro\Bundle\AddressBundle\Entity\Address")
     * @Soap\Result(phpType = "boolean")
     */
    public function updateAction($id, $address)
    {
        $address = $this->getEntity('OroAddressBundle:Address', (int)$id);
        $form = $this->container->get('oro_address.form.address.api');
        $this->container->get('oro_soap.request')->fix($form->getName());
        return $this->processForm($address);
    }

    /**
     * @Soap\Method("deleteAddress")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
     */
    public function deleteAction($id)
    {
        $em = $this->getManager();
        $entity = $this->getEntity('OroAddressBundle:Address', (int)$id);

        $em->remove($entity);
        $em->flush();

        return true;
    }

    /**
     * @Soap\Method("getCountries")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Country[]")
     */
    public function getCountriesAction()
    {
        return $this->getManager()->getRepository('OroAddressBundle:Country')->findAll();
    }

    /**
     * @Soap\Method("getCountry")
     * @Soap\Param("iso2Code", phpType = "string")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Country")
     */
    public function getCountryAction($iso2Code)
    {
        return $this->getEntity('OroAddressBundle:Country', $iso2Code);
    }

    /**
     * Shortcut to get entity
     *
     * @param string $repo
     * @param int|string $id
     * @throws \SoapFault
     * @return Address
     */
    protected function getEntity($repo, $id)
    {
        $entity = $this->getManager()->find($repo, $id);

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('Record #%u can not be found', $id));
        }

        return $entity;
    }

    /**
     * Form processing
     *
     * @param Address $entity Entity object
     * @return bool True on success
     * @throws \SoapFault
     */
    protected function processForm($entity)
    {
        if (!$this->container->get('oro_address.form.handler.address.api')->process($entity)) {
            throw new \SoapFault('BAD_REQUEST', $this->getFormErrors($this->container->get('oro_address.form.address.api')));
        }

        return true;
    }

    /**
     * @param Form $form
     * @return string All form's error messages concatenated into one string
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
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }
}
