<?php

namespace Oro\Bundle\UIBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\UIBundle\Form\DataTransformer\ArrayToStringTransformer;
use Oro\Bundle\UIBundle\Form\DataTransformer\IdsToEntitiesTransformer;
use Oro\Bundle\UIBundle\Form\EventListener\FixArrayToStringListener;

class EntityIdentifierType extends AbstractType
{
    const NAME = 'oro_entity_identifier';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addViewTransformer(
                new IdsToEntitiesTransformer(
                    $options['em'],
                    $options['class'],
                    $options['property'],
                    $options['queryBuilder']
                )
            )
            ->addViewTransformer(new ArrayToStringTransformer($options['values_delimiter'], true))
            ->addEventSubscriber(new FixArrayToStringListener($options['values_delimiter']));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'em'               => null,
                'property'         => null,
                'queryBuilder'     => null,
                'multiple'         => true,
                'values_delimiter' => ','
            )
        )
        ->setAllowedValues(
            array(
                'multiple' => array(true), // working with single entity is not supported yet
            )
        );
        $resolver->setRequired(array('class'));

        $registry = $this->registry;
        $emNormalizer = function (Options $options, $em) use ($registry) {
            if (null !== $em) {
                if ($em instanceof EntityManager) {
                    return $em;
                } else {
                    return $registry->getManager($em);
                }
            }

            $em = $registry->getManagerForClass($options['class']);

            if (null === $em) {
                throw new FormException(
                    sprintf(
                        'Class "%s" seems not to be a managed Doctrine entity. Did you forget to map it?',
                        $options['class']
                    )
                );
            }

            return $em;
        };

        $resolver->setNormalizers(
            array(
                'em' => $emNormalizer,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'hidden';
    }
}
