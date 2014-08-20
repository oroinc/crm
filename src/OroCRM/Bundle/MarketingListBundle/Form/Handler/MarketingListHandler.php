<?php

namespace OroCRM\Bundle\MarketingListBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator;

use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\SegmentBundle\Entity\SegmentType;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;

class MarketingListHandler
{
    /**
     * @var array
     */
    protected $marketingListTypeToSegmentTypeMap = array(
        MarketingListType::TYPE_DYNAMIC => SegmentType::TYPE_DYNAMIC,
        MarketingListType::TYPE_STATIC => SegmentType::TYPE_STATIC
    );

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
     * @var Validator
     */
    protected $validator;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param RegistryInterface $doctrine
     * @param Validator $validator
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        RegistryInterface $doctrine,
        Validator $validator
    ) {
        $this->form = $form;
        $this->request = $request;
        $this->manager = $doctrine->getManager();
        $this->validator = $validator;
    }

    /**
     * Process form
     *
     * @param  MarketingList $entity
     * @return bool True on successful processing, false otherwise
     */
    public function process(MarketingList $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($this->request);
            $this->processSegment($entity);

            if ($this->isValid($entity)) {
                $this->onSuccess($entity);
                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param MarketingList $entity
     */
    protected function onSuccess(MarketingList $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * @param MarketingList $marketingList
     */
    protected function processSegment(MarketingList $marketingList)
    {
        $requestData = $this->request->get($this->form->getName());
        $segment = $marketingList->getSegment();
        if (!$segment) {
            $segment = new Segment();
        }
        $segmentName = sprintf('Marketing List %s segment', $marketingList->getName());
        $segment
            ->setName($segmentName)
            ->setEntity($marketingList->getEntity())
            ->setType($this->getSegmentTypeByMarketingListType($marketingList->getType()))
            ->setDefinition($requestData['definition'])
            ->setOwner($marketingList->getOwner()->getOwner());

        $marketingList->setSegment($segment);
    }

    /**
     * @param MarketingListType $marketingListType
     * @return SegmentType
     */
    protected function getSegmentTypeByMarketingListType(MarketingListType $marketingListType)
    {
        $segmentTypeName = $this->marketingListTypeToSegmentTypeMap[$marketingListType->getName()];
        return $this->manager->find('OroSegmentBundle:SegmentType', $segmentTypeName);
    }

    /**
     * Validate Marketing List.
     *
     * @param MarketingList $marketingList
     * @return bool
     */
    protected function isValid(MarketingList $marketingList)
    {
        $errors = $this->validator->validate($marketingList->getSegment());
        if (count($errors) > 0) {
            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $this->form->addError(
                    new FormError(
                        $error->getMessage(),
                        $error->getMessageTemplate(),
                        $error->getMessageParameters(),
                        $error->getMessagePluralization()
                    )
                );
            }
        }

        return $this->form->isValid();
    }
}
