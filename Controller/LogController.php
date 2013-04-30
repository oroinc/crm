<?php

namespace Oro\Bundle\DataAuditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\DataAuditBundle\Entity\Log;

/**
 * @Acl(
 *      id="oro_dataaudit",
 *      name="Data audit",
 *      description="Data audit"
 * )
 */
class LogController extends Controller
{
    /**
     * @Route("/", name="oro_dataaudit_index")
     * @Template
     * @Acl(
     *      id="oro_dataaudit_list",
     *      name="View log stream",
     *      description="View log stream",
     *      parent="oro_dataaudit"
     * )
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/history/{entity}/{id}", name="oro_dataaudit_history", requirements={"entity"="[a-zA-Z\\]+", "id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_dataaudit_history",
     *      name="View entity history",
     *      description="View entity history log",
     *      parent="oro_dataaudit"
     * )
     */
    public function historyAction($entity, $id)
    {
        $history = $this->getManager()->getRepository('OroDataAuditBundle:Log')->findBy(array(
            'objectClass' => $entity,
            'objectId'    => $id,
        ));

        return array(
            'history' => $history,
        );
    }

    /**
     * @Route("/show/{id}", name="oro_dataaudit_show", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_dataaudit_show",
     *      name="View event",
     *      description="View event with changed data",
     *      parent="oro_dataaudit"
     * )
     */
    public function showAction(Log $entry)
    {
        return array(
            'entry' => $entry,
        );
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManagerForClass('OroDataAuditBundle:Log');
    }
}
