<?php

namespace Oro\Bundle\SalesBundle\Form\Handler;

use Oro\Bundle\SalesBundle\Entity\SalesFunnel;
use Symfony\Component\Form\FormInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @deprecated since 2.0 will be removed after 2.2
 */
class SalesFunnelHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param RequestStack  $requestStack
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, RequestStack $requestStack, ObjectManager $manager)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  SalesFunnel $entity
     * @return bool        True on successful processing, false otherwise
     */
    public function process(SalesFunnel $entity)
    {
        $this->form->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param SalesFunnel $entity
     */
    protected function onSuccess(SalesFunnel $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
