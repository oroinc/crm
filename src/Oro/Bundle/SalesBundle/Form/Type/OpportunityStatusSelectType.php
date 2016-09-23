<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityStatusSelectType extends AbstractType
{
    /** @var ConfigManager $configManager */
    private $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['probabilities'])) {
            $view->vars['attr']['data-probabilities'] = json_encode($options['probabilities']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $probabilities = $this->configManager->get(Opportunity::PROBABILITIES_CONFIG_KEY);

        // filter out statuses without probability
        $probabilities = array_filter($probabilities, function ($probability) {
            return null !== $probability;
        });

        // expose as percents
        $probabilities = array_map(
            function ($probability) {
                return round($probability * 100);
            },
            $probabilities
        );

        $resolver->setDefaults(['probabilities' => $probabilities]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_enum_select';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_sales_opportunity_status_select';
    }
}
