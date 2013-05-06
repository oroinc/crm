<?php

namespace Oro\Bundle\AccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use YsTools\BackUrlBundle\Annotation\BackUrl;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Entity\Manager\AccountManager;
use Oro\Bundle\AccountBundle\Datagrid\AccountDatagridManager;

/**
 * @Acl(
 *      id="oro_account_account",
 *      name="Account controller",
 *      description="Account manipulation",
 *      parent="oro_account"
 * )
 */
class AccountController extends Controller
{
    /**
     * @Route("/show/{id}", name="oro_account_show", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_account_account_show",
     *      name="View account",
     *      description="View account",
     *      parent="oro_account_account"
     * )
     * @BackUrl("back")
     */
    public function showAction(Account $account)
    {
        return array(
            'account' => $account,
        );
    }

    /**
     * Create account form
     *
     * @Route("/create", name="oro_account_create")
     * @Template("OroAccountBundle:Account:edit.html.twig")
     * @Acl(
     *      id="oro_account_account_create",
     *      name="Create account",
     *      description="Create account",
     *      parent="oro_account_account"
     * )
     */
    public function createAction()
    {
        $account = $this->getManager()->createFlexible();
        return $this->editAction($account);
    }

    /**
     * Edit user form
     *
     * @Route("/edit/{id}", name="oro_account_edit", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_account_account_edit",
     *      name="Edit account",
     *      description="Edit account",
     *      parent="oro_account_account"
     * )
     * @BackUrl("back")
     */
    public function editAction(Account $entity)
    {
        $backUrl = $this->getRedirectUrl($this->generateUrl('oro_account_index'));

        if ($this->get('oro_account.form.handler.account')->process($entity)) {
            $this->getFlashBag()->add('success', 'Account successfully saved');
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
     * @return AccountManager
     */
    protected function getManager()
    {
        return $this->get('oro_account.account.manager');
    }
}
