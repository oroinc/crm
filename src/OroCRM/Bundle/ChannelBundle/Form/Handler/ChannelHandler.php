<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Handler;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * Class ChannelHandler
 *
 * @package OroCRM\Bundle\ChannelBundle\Form\Handler
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class ChannelHandler
{
    /** @var Request */
    protected $request;

    /** @var EntityManager */
    protected $em;

    /** @var FormInterface */
    protected $form;

    /**
     * @param Request       $request
     * @param FormInterface $form
     * @param EntityManager $em
     */
    public function __construct(Request $request, FormInterface $form, EntityManager $em)
    {
        $this->request         = $request;
        $this->form            = $form;
        $this->em              = $em;
    }

    /**
     * @param Channel $entity
     *
     * @return bool
     */
    public function process(Channel $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->em->persist($entity);
                $this->em->flush();

                return true;
            }
        }

        return false;
    }
}
