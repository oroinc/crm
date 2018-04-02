<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Oro\Bundle\ChannelBundle\Form\DataTransformer\DatasourceDataTransformer;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChannelDatasourceType extends AbstractType
{
    const NAME = 'oro_channel_datasource_form';

    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $integrationEntityFQCN;

    /**
     * @param ManagerRegistry $registry
     * @param string          $integrationEntityFQCN
     */
    public function __construct(ManagerRegistry $registry, $integrationEntityFQCN)
    {
        $this->registry              = $registry;
        $this->integrationEntityFQCN = $integrationEntityFQCN;
    }

    /**
     * {@inheritdoc}
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
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $em          = $this->registry->getManagerForClass($this->integrationEntityFQCN);
        $formFactory = $builder->getFormFactory();

        $data = $builder->create('data', HiddenType::class);
        $data->addViewTransformer(new ArrayToJsonTransformer());
        $identifier = $builder->create('identifier', HiddenType::class);
        $identifier->addViewTransformer(new EntityToIdTransformer($em, $this->integrationEntityFQCN));
        $builder->addViewTransformer(new DatasourceDataTransformer($formFactory));

        $builder->add($data);
        $builder->add($identifier);
        $builder->add('type', HiddenType::class, ['data' => $options['type']]);
        $builder->add('name', HiddenType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['type']);
    }
}
