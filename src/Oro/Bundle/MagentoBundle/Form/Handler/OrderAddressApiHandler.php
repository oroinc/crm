<?php

namespace Oro\Bundle\MagentoBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderAddressApiHandler extends ApiFormHandler
{
    /** @var Organization */
    protected $organization;

    /**
     * @param FormInterface          $form
     * @param RequestStack           $requestStack
     * @param ObjectManager          $entityManager
     * @param TokenAccessorInterface $security
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        ObjectManager $entityManager,
        TokenAccessorInterface $security
    ) {
        parent::__construct($form, $requestStack, $entityManager);
        $this->organization = $security->getOrganization();
    }

    /**
     * {@inheritdoc}
     */
    protected function onSuccess($entity)
    {
        if (null === $entity->getOrganization()) {
            $entity->setOrganization($this->organization);
        }
        parent::onSuccess($entity);
    }
}
