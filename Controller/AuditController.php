<?php

namespace Oro\Bundle\DataAuditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\DataAuditBundle\Entity\Audit;

/**
 * @Acl(
 *      id="oro_dataaudit",
 *      name="Data audit",
 *      description="Data audit"
 * )
 */
class AuditController extends Controller
{
    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_dataaudit_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_dataaudit_index",
     *      name="View audit stream",
     *      description="View audit stream",
     *      parent="oro_dataaudit"
     * )
     */
    public function indexAction(Request $request)
    {
        $datagrid = $this->get('oro_dataaudit.datagrid.manager')->getDatagrid();
        $view     = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroDataAuditBundle:Audit:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * @Route("/history/{entity}/{id}", name="oro_dataaudit_history", requirements={"entity"="[a-zA-Z\\]+", "id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_dataaudit_history",
     *      name="View entity history",
     *      description="View entity history audit log",
     *      parent="oro_dataaudit"
     * )
     */
    public function historyAction($entity, $id)
    {
        $history = $this->getDoctrine()
            ->getManagerForClass('OroDataAuditBundle:Audit')
            ->getRepository('OroDataAuditBundle:Audit')->findBy(
                array(
                    'objectClass' => $entity,
                    'objectId'    => $id,
                ),
                array('id' => 'DESC')
            );

        return array(
            'history' => $history,
        );
    }
}
