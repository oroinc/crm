<?php

namespace Oro\Bundle\AccountBundle\Controller;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use YsTools\BackUrlBundle\Annotation\BackUrl;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Datagrid\AccountDatagridManager;

/**
 * @Acl(
 *      id="oro_account_account",
 *      name="Account controller",
 *      description="Account manipulation",
 *      parent="oro_account"
 * )
 * @BackUrl("back", useSession=true)
 */
class AccountController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_account_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_account_account_view",
     *      name="View account",
     *      description="View account",
     *      parent="oro_account_account"
     * )
     * @BackUrl("back")
     */
    public function viewAction(Account $account)
    {
        return array(
            'account' => $account,
        );
    }

    /**
     * Create account form
     *
     * @Route("/create", name="oro_account_create")
     * @Template("OroAccountBundle:Account:update.html.twig")
     * @Acl(
     *      id="oro_account_account_create",
     *      name="Create account",
     *      description="Create account",
     *      parent="oro_account_account"
     * )
     */
    public function createAction()
    {
        /** @var Account $account */
        $account = $this->getManager()->createEntity();
        return $this->updateAction($account);
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="oro_account_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_account_account_update",
     *      name="Edit account",
     *      description="Edit account",
     *      parent="oro_account_account"
     * )
     */
    public function updateAction(Account $entity)
    {
        $backUrl = $this->generateUrl('oro_account_index');

        if ($this->get('oro_account.form.handler.account')->process($entity)) {
            $this->getFlashBag()->add('success', 'Account successfully saved');
            BackUrl::triggerRedirect();
            return $this->redirect($backUrl);
        }

        return array(
            'form' => $this->get('oro_account.form.account')->createView(),
        );
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_account_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_account_account_list",
     *      name="View list of accounts",
     *      description="View list of accounts",
     *      parent="oro_account_account"
     * )
     * @BackUrl("back")
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager AccountDatagridManager */
        $gridManager = $this->get('oro_account.account.datagrid_manager');
        $datagrid = $gridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'OroAccountBundle:Account:index.html.twig';
        }

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
        );
    }

    /**
     * @return FlashBag
     */
    protected function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }

    /**
     * @return ApiFlexibleEntityManager
     */
    protected function getManager()
    {
        return $this->get('oro_account.account.manager.api');
    }
}
