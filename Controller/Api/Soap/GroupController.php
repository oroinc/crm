<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use Symfony\Component\DependencyInjection\ContainerAware;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

class GroupController extends ContainerAware
{
    /**
     * @Soap\Method("getGroups")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Group[]")
     */
    public function сgetAction()
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
             $this->getManager()
                ->createQuery('SELECT g FROM OroUserBundle:Group g ORDER BY g.id')
                ->getResult()
        );
    }

    /**
     * @Soap\Method("getGroup")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Group")
     */
    public function getAction($id)
    {
        $entity = $this->getManager()->find('OroUserBundle:Group', (int) $id);

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('The group #%u can not be found', $id));
        }

        return $this->container->get('besimple.soap.response')->setReturnValue($entity);
    }

    /**
     * @Soap\Method("deleteGroup")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
     */
    public function deleteAction($id)
    {
        $em     = $this->getManager();
        $entity = $em->find('OroUserBundle:Group', (int) $id);

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('The group #%u can not be found', $id));
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
