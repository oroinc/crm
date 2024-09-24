<?php

namespace Oro\Bundle\CaseBundle\Form\Type;

use Oro\Bundle\TagBundle\Form\Type\TagSelectType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CaseMailboxProcessSettingsTagType extends AbstractType
{
    /** @var EventSubscriberInterface */
    protected $oldTagSubscriber;

    /** @var EventSubscriberInterface */
    protected $newTagSubscriber;

    public function __construct(EventSubscriberInterface $oldTagSubscriber, EventSubscriberInterface $newTagSubscriber)
    {
        $this->oldTagSubscriber = $oldTagSubscriber;
        $this->newTagSubscriber = $newTagSubscriber;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->getEventDispatcher()->removeSubscriber($this->oldTagSubscriber);
        $builder->addEventSubscriber($this->newTagSubscriber);
    }

    #[\Override]
    public function getParent(): ?string
    {
        return TagSelectType::class;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'case_mailbox_process_settings_tag';
    }
}
