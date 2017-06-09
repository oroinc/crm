<?php

namespace Oro\Bundle\ContactUsBundle\Controller;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\ContactUsBundle\Form\Type\ContactReasonType;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class ContactReasonController extends Controller
{
    /**
     * @Route("/", name="oro_contactus_reason_index")
     * @Template
     * @AclAncestor("oro_contactus_reason_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => ContactReason::class
        ];
    }

    /**
     * @return array|RedirectResponse
     *
     * @Route("/create", name="oro_contactus_reason_create")
     * @Template("OroContactUsBundle:ContactReason:update.html.twig")
     * @Acl(
     *      id="oro_contactus_reason_create",
     *      type="entity",
     *      class="OroContactUsBundle:ContactReason",
     *      permission="CREATE"
     * )
     */
    public function createAction()
    {
        return $this->update(new ContactReason());
    }

    /**
     * @ParamConverter("contactReason", options={"repository_method" = "getContactReason"})
     *
     * @param ContactReason $contactReason
     * @return array
     *
     * @Route("/update/{id}", name="oro_contactus_reason_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_contactus_reason_update",
     *      type="entity",
     *      class="OroContactUsBundle:ContactReason",
     *      permission="EDIT"
     * )
     */
    public function updateAction(ContactReason $contactReason)
    {
        return $this->update($contactReason);
    }

    /**
     * @param ContactReason $contactReason
     *
     * @return array|RedirectResponse
     */
    protected function update(ContactReason $contactReason)
    {
        return $this->get('oro_form.model.update_handler')->update(
            $contactReason,
            $this->createForm(ContactReasonType::class, $contactReason),
            $this->get('translator')->trans('oro.contactus.contactreason.saved')
        );
    }

    /**
     * @ParamConverter("contactReason", options={"repository_method" = "getContactReason"})
     *
     * @param ContactReason $contactReason
     * @return JsonResponse
     *
     * @Route("/delete/{id}", name="oro_contactus_reason_delete", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_contactus_reason_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroContactUsBundle:ContactReason"
     * )
     */
    public function deleteAction(ContactReason $contactReason)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $em->remove($contactReason);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }
}
