<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeadStatusSelectType extends AbstractType
{
    const NAME = 'orocrm_type_widget_lead_status_select';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
            [
                'choices'  => $this->getChoices(),
                'multiple' => true,
                'configs'  => [
                    'width'      => '400px',
                    'allowClear' => true,
                ]
            ]
        );
    }

    /**
     * @return array
     */
    protected function getChoices()
    {
        $repository = $this->entityManager->getRepository('OroCRMSalesBundle:LeadStatus');

        $result = $repository->createQueryBuilder('ls')
            ->getQuery()
            ->getArrayResult();

        $choices = [];

        foreach ($result as $item) {
            $choices[$item['name']] = $item['label'];
        }

        return $choices;
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
        return 'genemu_jqueryselect2_choice';
    }
}
