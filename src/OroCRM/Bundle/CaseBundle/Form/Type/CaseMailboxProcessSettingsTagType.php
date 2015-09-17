<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CaseMailboxProcessSettingsTagType extends AbstractType
{
    /** @var EventSubscriberInterface */
    protected $oldTagSubscriber;

    /** @var EventSubscriberInterface */
    protected $newTagSubscriber;

    /**
     * @param EventSubscriberInterface $oldTagSubscriber
     * @param EventSubscriberInterface $newTagSubscriber
     */
    public function __construct(EventSubscriberInterface $oldTagSubscriber, EventSubscriberInterface $newTagSubscriber)
    {
        $this->oldTagSubscriber = $oldTagSubscriber;
        $this->newTagSubscriber = $newTagSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->getEventDispatcher()->removeSubscriber($this->oldTagSubscriber);
        $builder->addEventSubscriber($this->newTagSubscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_tag_select';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'case_mailbox_process_settings_tag';
    }
}
