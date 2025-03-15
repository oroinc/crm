<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Form\DataTransformer\DatasourceDataTransformer;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type to select channel datasource.
 */
class ChannelDatasourceType extends AbstractType
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private string $integrationEntityClass
    ) {
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_channel_datasource_form';
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formFactory = $builder->getFormFactory();

        $data = $builder->create('data', HiddenType::class);
        $data->addViewTransformer(new ArrayToJsonTransformer());
        $identifier = $builder->create('identifier', HiddenType::class);
        $identifier->addViewTransformer(new EntityToIdTransformer($this->doctrine, $this->integrationEntityClass));
        $builder->addViewTransformer(new DatasourceDataTransformer($formFactory));

        $builder->add($data);
        $builder->add($identifier);
        $builder->add('type', HiddenType::class, ['data' => $options['type']]);
        $builder->add('name', HiddenType::class);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['type']);
    }
}
