<?php

namespace Oro\Bundle\AccountBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The handler for account form.
 */
class AccountHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    protected ObjectManager $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function process($entity, FormInterface $form, Request $request)
    {
        $form->setData($entity);
        if (\in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($form, $request);

            if ($form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     */
    protected function onSuccess(Account $entity): void
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
