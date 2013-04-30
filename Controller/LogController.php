<?php

namespace Oro\Bundle\DataAuditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use YsTools\BackUrlBundle\Annotation\BackUrl;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

//use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\DataAuditBundle\Entity\Log;
use Oro\Bundle\DataAuditBundle\Datagrid\LogDatagridManager;

class LogController extends Controller
{
    /**
     * @Route(
     *      "/index",
     *      name="oro_dataaudit_index"
     * )
     * @BackUrl("back")
     */
    public function indexAction(Request $request)
    {
        $datagrid = $this->get('oro_dataaudit.log_datagrid.manager')->getDatagrid();
        $view     = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroDataAuditBundle:Log:index.html.twig';

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
        );

    }

    /**
     * Get redirect URLs
     *
     * @param  string $default
     * @return string
     */
    protected function getRedirectUrl($default)
    {
        $flashBag = $this->getFlashBag();
        if ($this->getRequest()->query->has('back')) {
            $backUrl = $this->getRequest()->get('back');
            $flashBag->set('backUrl', $backUrl);
        } elseif ($flashBag->has('backUrl')) {
            $backUrl = $flashBag->get('backUrl');
            $backUrl = reset($backUrl);
        } else {
            $backUrl = null;
        }

        return $backUrl ? $backUrl : $default;
    }

    /**
     * @return FlashBag
     */
    protected function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }

    /**
     * @return LogManager
     */
    protected function getManager()
    {
        return $this->get('oro_dataaudit.log_datagrid.manager');
    }
}
