<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use Symfony\Component\DependencyInjection\ContainerAware;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\UserBundle\Entity\Role;

class RoleController extends ContainerAware
{
    /**
     * @Soap\Method("getRoles")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Role[]")
     */
    public function сgetAction()
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
             $this->getManager()
                ->createQuery('SELECT r FROM OroUserBundle:Role r ORDER BY r.id')
                ->getResult()
        );
    }

    /**
     * @Soap\Method("getRole")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Role")
     */
    public function getAction($id)
    {
        $entity = $this->getManager()->find('OroUserBundle:Role', (int) $id);

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('The role #%u can not be found', $id));
        }

        return $this->container->get('besimple.soap.response')->setReturnValue($entity);
    }

    /**
     * @Soap\Method("deleteRole")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
     */
    public function deleteAction($id)
    {
        $em     = $this->getManager();
        $entity = $em->find('OroUserBundle:Role', (int) $id);

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('The role #%u can not be found', $id));
        }

        $em->remove($entity);
        $em->flush();

        return $this->container->get('besimple.soap.response')->setReturnValue(true);
    }

    /**
     * @return Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }
}
