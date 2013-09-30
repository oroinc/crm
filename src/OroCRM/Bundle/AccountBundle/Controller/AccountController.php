<?php

namespace OroCRM\Bundle\AccountBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Query;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\AccountBundle\Datagrid\AccountDatagridManager;
use OroCRM\Bundle\AccountBundle\Datagrid\AccountContactDatagridManager;
use OroCRM\Bundle\AccountBundle\Datagrid\AccountContactUpdateDatagridManager;

class AccountController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_account_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_account_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMAccountBundle:Account"
     * )
     */
    public function viewAction(Account $account)
    {
        /** @var $contactDatagridManager AccountContactDatagridManager */
        $contactDatagridManager = $this->get('orocrm_account.contact.view_datagrid_manager');
        $contactDatagridManager->setAccount($account);
        $datagridView = $contactDatagridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array(
            'entity'   => $account,
            'datagrid' => $datagridView,
        );
    }

    /**
     * Create account form
     *
     * @Route("/create", name="orocrm_account_create")
     * @Template("OroCRMAccountBundle:Account:update.html.twig")
     * @Acl(
     *      id="orocrm_account_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMAccountBundle:Account"
     * )
     */
    public function createAction()
    {
        return $this->update();
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="orocrm_account_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_account_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMAccountBundle:Account"
     * )
     */
    public function updateAction(Account $entity = null)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/contact/select/{id}",
     *      name="orocrm_account_contact_select",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @Template
     * @AclAncestor("orocrm_contact_view")
     */
    public function contactDatagridAction(Account $entity = null)
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }
        /** @var $datagridManager AccountContactUpdateDatagridManager */
        $datagridManager = $this->get('orocrm_account.contact.update_datagrid_manager');
        $datagridManager->setAccount($entity);
        $datagridManager->setAdditionalParameters(
            array(
                'data_in' => explode(',', $this->getRequest()->get('added')),
                'data_not_in' => explode(',', $this->getRequest()->get('removed'))
            )
        );
        $datagridView = $datagridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array(
            'datagrid' => $datagridView,
        );
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_account_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @AclAncestor("orocrm_account_view")
     * @Template
     */
    public function indexAction()
    {
        /** @var $gridManager AccountDatagridManager */
        $gridManager = $this->get('orocrm_account.account.datagrid_manager');
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array('datagrid' => $datagridView);
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
        return $this->get('orocrm_account.account.manager.api');
    }

    /**
     * @param Account $entity
     * @return array
     */
    protected function update(Account $entity = null)
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        if ($this->get('orocrm_account.form.handler.account')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.account.controller.account.saved.message')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'orocrm_account_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route' => 'orocrm_account_view',
                    'parameters' => array('id' => $entity->getId())
                )
            );
        }

        return array(
            'form'     => $this->get('orocrm_account.form.account')->createView()
        );
    }
}
