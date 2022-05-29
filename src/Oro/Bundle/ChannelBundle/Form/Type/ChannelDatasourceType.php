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
    private ManagerRegistry $doctrine;
    private string $integrationEntityClass;

    public function __construct(ManagerRegistry $doctrine, string $integrationEntityClass)
    {
        $this->doctrine = $doctrine;
        $this->integrationEntityClass = $integrationEntityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_channel_datasource_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $em = $this->doctrine->getManagerForClass($this->integrationEntityClass);
        $formFactory = $builder->getFormFactory();

        $data = $builder->create('data', HiddenType::class);
        $data->addViewTransformer(new ArrayToJsonTransformer());
        $identifier = $builder->create('identifier', HiddenType::class);
        $identifier->addViewTransformer(new EntityToIdTransformer($em, $this->integrationEntityClass));
        $builder->addViewTransformer(new DatasourceDataTransformer($formFactory));

        $builder->add($data);
        $builder->add($identifier);
        $builder->add('type', HiddenType::class, ['data' => $options['type']]);
        $builder->add('name', HiddenType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['type']);
    }
}
