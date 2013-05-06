<?php

namespace Oro\Bundle\AccountBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\AccountBundle\Entity\Manager\AccountManager;
use Oro\Bundle\AccountBundle\Entity\Account;

/**
 * @NamePrefix("oro_api_")
 */
class AccountController extends FOSRestController implements ClassResourceInterface
{

    /**
     * Delete account
     *
     * @param int $id Account id
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Delete account",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @Acl(
     *      id="oro_account_account_remove",
     *      name="Remove account",
     *      description="Remove account",
     *      parent="oro_account_account"
     * )
     */
    public function deleteAction($id)
    {
        /** @var Account $entity */
        $entity = $this->getManager()->findAccountBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $this->getManager()->deleteAccount($entity);

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * @return AccountManager
     */
    protected function getManager()
    {
        return $this->get('oro_account.account.manager');
    }
}
