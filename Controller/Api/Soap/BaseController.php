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
}
