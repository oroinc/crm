<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Handler;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

class EmailCampaignHandler
{
    const UPDATE_MARKER = 'formUpdateMarker';

    /** @var Request */
    protected $request;

    /** @var RegistryInterface */
    protected $registry;

    /** @var FormInterface */
    protected $form;

    /**
     * @param Request $request
     * @param FormInterface $form
     * @param RegistryInterface $registry
     */
    public function __construct(
        Request $request,
        FormInterface $form,
        RegistryInterface $registry
    ) {
        $this->request = $request;
        $this->form = $form;
        $this->registry = $registry;
    }

    /**
     * Process form
     *
     * @param EmailCampaign $entity
     *
     * @return bool
     */
    public function process(EmailCampaign $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);
            if (!$this->request->get(self::UPDATE_MARKER, false) && $this->form->isValid()) {
                $em = $this->registry->getManagerForClass('OroCRMCampaignBundle:EmailCampaign');
                $em->persist($entity);
                $em->flush();

                return true;
            }
        }

        return false;
    }
}
