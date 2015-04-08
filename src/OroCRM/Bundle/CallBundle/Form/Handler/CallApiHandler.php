<?php

namespace OroCRM\Bundle\CallBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Tools\AssociationHelper;
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
                $this->handleAssociations($entity);
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * Add associations to call item
     *
     * @param Call $entity
     */
    protected function handleAssociations(Call $entity)
    {
        $associations = $this->form->get('associations');
        if (empty($associations)) {
            return;
        }
        foreach ($associations->getData() as $association) {
            if (!empty($association['entityName']) && !empty($association['entityId'])) {
                $associationType = isset($association['type']) ? $association['type'] : null;
                $target = $this->manager->getReference($association['entityName'], $association['entityId']);
                call_user_func(
                    [
                        $entity,
                        AssociationHelper::getManyToManySetterMethodName($associationType)
                    ],
                    $target
                );
            }
        }
    }

    /**
     * "Success" form handler
     *
     * @param Call $entity
     */
    protected function onSuccess(Call $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
