<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use Symfony\Component\DependencyInjection\ContainerAware;

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
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * Fix Request object so forms can be handled correctly
     *
     * @param string $name Form name
     */
    protected function fixRequest($name)
    {
        $data = $this->container->get('request')->get($name);

        if (is_object($data)) {
            $values = array();

            foreach ((array) $data as $prop => $value) {
                if (!is_null($value)) {
                    $values[preg_replace('/[^a-z]+/i', '', $prop)] = $value;
                }
            }

            $this->container->get('request')->request->set($name, $values);
        }
    }
}
