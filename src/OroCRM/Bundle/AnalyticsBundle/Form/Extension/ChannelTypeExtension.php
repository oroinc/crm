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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\AnalyticsBundle\Form\Type\RFMCategorySettingsType;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use OroCRM\Bundle\AnalyticsBundle\Validator\CategoriesConstraint;
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
     * @param string $rfmCategoryClass
     */
    public function __construct(DoctrineHelper $doctrineHelper, $interface, $rfmCategoryClass)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->interface = $interface;
        $this->rfmCategoryClass = $rfmCategoryClass;
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'loadCategories']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'handleState'], 10);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'manageCategories'], 20);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['validation_groups' => $this->getValidationGroups()]);
    }

    /**
     * @param FormEvent $event
     */
    public function manageCategories(FormEvent $event)
    {
        /** @var Channel $channel */
        $channel = $event->getData();

        if (!$this->isApplicable($channel)) {
            return;
        }

        $em = $this->doctrineHelper->getEntityManager($this->rfmCategoryClass);
        $form = $event->getForm();

        if (!$this->isRFMEnabled($form)) {
            return;
        }

        foreach (RFMMetricCategory::$types as $type) {
            if (!$form->has($type)) {
                continue;
            }

            /** @var PersistentCollection|RFMMetricCategory[] $categories */
            $child = $form->get($type);
            $categories = $child->getData();

            if (!$categories->isDirty()) {
                continue;
            }

            /** @var RFMMetricCategory $category */
            foreach ($categories->getInsertDiff() as $category) {
                $category
                    ->setCategoryType($type)
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
    public function handleState(FormEvent $event)
    {
        /** @var Channel $channel */
        $channel = $event->getData();
        if (!$this->isApplicable($channel)) {
            return;
        }

        $form = $event->getForm();
        if (!$form->has(RFMAwareInterface::RFM_STATE_KEY)) {
            return;
        }

        $rfmEnabled = $this->getRFMEnabled($form);
        $data = $channel->getData();
        if (!$data) {
            $data = [];
        }

        if (array_key_exists(RFMAwareInterface::RFM_STATE_KEY, $data)
            && $data[RFMAwareInterface::RFM_STATE_KEY] === $rfmEnabled
        ) {
            return;
        }

        if (!$rfmEnabled) {
            $data[RFMAwareInterface::RFM_REQUIRE_DROP_KEY] = true;
        }

        $data[RFMAwareInterface::RFM_STATE_KEY] = $rfmEnabled;

        $channel->setData($data);
        $event->setData($channel);
    }

    /**
     * @param FormEvent $event
     */
    public function loadCategories(FormEvent $event)
    {
        /** @var Channel $channel */
        $channel = $event->getData();

        if (!$this->isApplicable($channel)) {
            return;
        }

        $categories = $this->doctrineHelper
            ->getEntityRepository($this->rfmCategoryClass)
            ->findBy(
                ['channel' => $channel],
                ['categoryIndex' => Criteria::ASC]
            );

        $channelData = (array)$channel->getData();
        $rfmEnabled = !empty($channelData[RFMAwareInterface::RFM_STATE_KEY]);
        $form = $event->getForm();
        $form->add(
            RFMAwareInterface::RFM_STATE_KEY,
            'checkbox',
            [
                'label' => 'orocrm.analytics.form.rfm_enable.label',
                'mapped' => false,
                'required' => false,
                'data' => $rfmEnabled,
                'tooltip' => 'orocrm.analytics.rfm.tooltip'
            ]
        );
        $this->addRFMTypes($form, $categories);
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
                    return $category->getCategoryType() === $type;
                }
            );

            $collection = new PersistentCollection(
                $this->doctrineHelper->getEntityManager($this->rfmCategoryClass),
                $this->doctrineHelper->getEntityMetadata($this->rfmCategoryClass),
                new ArrayCollection($typeCategories)
            );

            $collection->takeSnapshot();

            $constraint = new CategoriesConstraint();
            $constraint->setType($type);

            $form->add(
                $type,
                RFMCategorySettingsType::NAME,
                [
                    RFMCategorySettingsType::TYPE_OPTION => $type,
                    'label' => sprintf('orocrm.analytics.form.%s.label', $type),
                    'tooltip' => sprintf('orocrm.analytics.%s.tooltip', $type),
                    'mapped' => false,
                    'required' => false,
                    'error_bubbling' => false,
                    'is_increasing' => $type === RFMMetricCategory::TYPE_RECENCY,
                    'constraints' => [$constraint],
                    'data' => $collection
                ]
            );
        }
    }

    /**
     * @param Channel $channel
     *
     * @return bool
     */
    protected function isApplicable(Channel $channel = null)
    {
        if (!$channel) {
            return false;
        }

        $customerIdentity = $channel->getCustomerIdentity();
        if (!$customerIdentity) {
            return false;
        }

        return in_array($this->interface, class_implements($customerIdentity), true);
    }

    /**
     * @return callable
     */
    protected function getValidationGroups()
    {
        return function (FormInterface $form) {
            if ($this->isRFMEnabled($form)) {
                return [CategoriesConstraint::DEFAULT_GROUP, CategoriesConstraint::GROUP];
            }

            return [CategoriesConstraint::DEFAULT_GROUP];
        };
    }

    /**
     * @param FormInterface $form
     *
     * @return bool
     */
    protected function isRFMEnabled(FormInterface $form)
    {
        if (!$form->has(RFMAwareInterface::RFM_STATE_KEY)) {
            return false;
        }

        return $this->getRFMEnabled($form);
    }

    /**
     * @param FormInterface $form
     *
     * @return bool
     */
    protected function getRFMEnabled(FormInterface $form)
    {
        if (!$form->has(RFMAwareInterface::RFM_STATE_KEY)) {
            throw new \InvalidArgumentException(sprintf('%s form child is missing'));
        }

        $data = $form->get(RFMAwareInterface::RFM_STATE_KEY)->getData();

        return filter_var($data, FILTER_VALIDATE_BOOLEAN);
    }
}
