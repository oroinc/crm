<?php

namespace OroCRM\Bundle\AnalyticsBundle\Form\Extension;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\PersistentCollection;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\AnalyticsBundle\Form\Type\RFMCategorySettingsType;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelType;

class ChannelTypeExtension extends AbstractTypeExtension
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $interface;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param string $interface
     */
    public function __construct(DoctrineHelper $doctrineHelper, $interface)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->interface = $interface;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ChannelType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $em = $this->doctrineHelper->getEntityManager('OroCRMAnalyticsBundle:RFMMetricCategory');
        $form = $event->getForm();
        /** @var Channel $channel */
        $channel = $this->doctrineHelper->getEntityReference(
            $this->doctrineHelper->getEntityClass($event->getData()),
            $this->doctrineHelper->getSingleEntityIdentifier($event->getData())
        );

        foreach (RFMMetricCategory::$types as $type) {
            /** @var PersistentCollection $categories */
            $categories = $form->get($type)->getData();

            /** @var RFMMetricCategory $category */
            foreach ($categories->getInsertDiff() as $category) {
                $category
                    ->setType($type)
                    ->setChannel($channel);
                $em->persist($category);
            }

            foreach ($categories->getDeleteDiff() as $category) {
                $em->remove($category);
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var Channel $channel */
        $channel = $event->getData();
        if (!$channel) {
            return;
        }

        $customerIdentity = $channel->getCustomerIdentity();
        if (!in_array($this->interface, class_implements($customerIdentity))) {
            return;
        }

        $categories = $this->doctrineHelper
            ->getEntityRepository('OroCRMAnalyticsBundle:RFMMetricCategory')
            ->findBy(
                ['channel' => $channel],
                ['index' => Criteria::ASC]
            );

        $this->addRFMTypes($event->getForm(), $categories);
    }

    /**
     * @param FormInterface $form
     * @param array $categories
     */
    protected function addRFMTypes(FormInterface $form, array $categories)
    {
        foreach (RFMMetricCategory::$types as $type) {
            $typeCategories = array_filter(
                $categories,
                function (RFMMetricCategory $category) use ($type) {
                    return $category->getType() === $type;
                }
            );

            $collection = new PersistentCollection(
                $this->doctrineHelper->getEntityManager('OroCRMAnalyticsBundle:RFMMetricCategory'),
                $this->doctrineHelper->getEntityMetadata('OroCRMAnalyticsBundle:RFMMetricCategory'),
                new ArrayCollection($typeCategories)
            );

            $collection->takeSnapshot();

            $form->add(
                $type,
                RFMCategorySettingsType::NAME,
                [
                    RFMCategorySettingsType::TYPE_OPTION => $type,
                    'mapped' => false,
                    'required' => false,
                    'data' => $collection,
                ]
            );
        }
    }
}
