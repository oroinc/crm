<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Oro\Bundle\ChannelBundle\Form\DataTransformer\DatasourceDataTransformer;

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

        $data = $builder->create('data', 'hidden');
        $data->addViewTransformer(new ArrayToJsonTransformer());
        $identifier = $builder->create('identifier', 'hidden');
        $identifier->addViewTransformer(new EntityToIdTransformer($em, $this->integrationEntityFQCN));
        $builder->addViewTransformer(new DatasourceDataTransformer($formFactory));

        $builder->add($data);
        $builder->add($identifier);
        $builder->add('type', 'hidden', ['data' => $options['type']]);
        $builder->add('name', 'hidden');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['type']);
    }
}
