<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Oro\Bundle\SecurityBundle\SecurityFacade;

class CustomerGroupSelectType extends AbstractType
{
    const NAME = 'orocrm_magento_customer_group_select';

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var boolean
     */
    protected $canAssignChannel;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
        $this->canAssignChannel = $this->securityFacade->isGranted('oro_integration_assign');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'magento_customer_group',
                'grid_name' => 'magento-customers-group-by-channel-grid',
                'configs' => [
                    'placeholder' => 'orocrm.magento.customergroup.placeholder'
                ]
            ]
        );

        // Set store form type readonly if ASSIGN permissions for integration not set
        $resolver->setNormalizers(
            [
                'disabled' => function (Options $options, $value) {
                    return $this->isReadOnly($options) ? true : $value;
                },
                'validation_groups' => function (Options $options, $value) {
                    return $options['disabled'] ? false : $value;
                },
            ]
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
        return 'oro_entity_create_or_select_inline_channel_aware';
    }

    /**
     * Checks if the form type should be read-only or not
     *
     * @param array $options
     *
     * @return bool
     */
    protected function isReadOnly($options)
    {
        if (!$this->canAssignChannel) {
            return true;
        }
        return false;
    }
}
