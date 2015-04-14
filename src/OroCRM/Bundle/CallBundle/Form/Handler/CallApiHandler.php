<?php

namespace OroCRM\Bundle\CallBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Tools\AssociationNameGenerator;
use OroCRM\Bundle\CallBundle\Entity\Call;

class CallApiHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param Request       $request
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  Call $entity
     * @return bool  True on successful processing, false otherwise
     */
    public function process(Call $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

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
     * @param Call $entity
     */
    protected function onSuccess(Call $entity)
    {
        $this->handleAssociations($entity);
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Add associations to call item
     *
     * @param Call $entity
     */
    protected function handleAssociations(Call $entity)
    {
        $associationsFormField = $this->form->get('associations');
        if (!$associationsFormField) {
            return;
        }
        $associations = $associationsFormField->getData();
        if (empty($associations)) {
            return;
        }
        foreach ($associations as $association) {
            $associationType = isset($association['type']) ? $association['type'] : null;
            $target          = $this->manager->getReference($association['entityName'], $association['entityId']);
            call_user_func(
                [
                    $entity,
                    AssociationNameGenerator::generateAddTargetMethodName($associationType)
                ],
                $target
            );
        }
    }
}
