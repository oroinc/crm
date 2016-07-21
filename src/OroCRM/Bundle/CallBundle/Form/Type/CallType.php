<?php

namespace OroCRM\Bundle\CallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;

use OroCRM\Bundle\CallBundle\Entity\Call;

class CallType extends AbstractType
{
    /** @var PhoneProviderInterface */
    protected $phoneProvider;

    /**
     * @param PhoneProviderInterface $phoneProvider
     */
    public function __construct(PhoneProviderInterface $phoneProvider)
    {
        $this->phoneProvider = $phoneProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'subject',
                'text',
                [
                    'required' => true,
                    'label'    => 'orocrm.call.subject.label'
                ]
            )
            ->add(
                'phoneNumber',
                'orocrm_call_phone',
                [
                    'required'    => true,
                    'label'       => 'orocrm.call.phone_number.label',
                    'suggestions' => $options['phone_suggestions']
                ]
            )
            ->add(
                'notes',
                'oro_resizeable_rich_text',
                [
                    'required' => false,
                    'label'    => 'orocrm.call.notes.label'
                ]
            )
            ->add(
                'callDateTime',
                'oro_datetime',
                [
                    'required' => true,
                    'label'    => 'orocrm.call.call_date_time.label'
                ]
            )
            ->add(
                'callStatus',
                'translatable_entity',
                [
                    'required' => true,
                    'label'    => 'orocrm.call.call_status.label',
                    'class'    => 'OroCRM\Bundle\CallBundle\Entity\CallStatus'
                ]
            )
            ->add(
                'duration',
                'oro_duration',
                [
                    'required' => false,
                    'label'    => 'orocrm.call.duration.label'
                ]
            )
            ->add(
                'direction',
                'translatable_entity',
                [
                    'required' => true,
                    'label'    => 'orocrm.call.direction.label',
                    'class'    => 'OroCRM\Bundle\CallBundle\Entity\CallDirection'
                ]
            );

        if ($builder->has('contexts')) {
            $builder->addEventListener(
                FormEvents::POST_SET_DATA,
                [$this, 'addPhoneContextListener']
            );
        }
    }

    /**
     * Adds phone number owner to default contexts
     *
     * @param FormEvent $event
     */
    public function addPhoneContextListener(FormEvent $event)
    {
        /** @var Call $entity */
        $entity = $event->getData();
        $form   = $event->getForm();

        if (!is_object($entity) || $entity->getId()) {
            return;
        }

        $contexts = $form->get('contexts')->getData();
        $phoneContexts = [];

        foreach ($contexts as $targetEntity) {
            $phones = $this->phoneProvider->getPhoneNumbers($targetEntity);
            foreach ($phones as $phone) {
                if ($entity->getPhoneNumber() === $phone[0]) {
                    $phoneContexts[] = $phone[1];
                }
            }
        }

        $form->get('contexts')->setData(array_merge($contexts, $phoneContexts));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'        => 'OroCRM\Bundle\CallBundle\Entity\Call',
                'phone_suggestions' => []
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_call_form';
    }
}
