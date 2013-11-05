<?php

namespace OroCRM\Bundle\AccountBundle\Controller;

use Doctrine\ORM\Query;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager;

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
        return ['entity' => $account];
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
        return [];
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
