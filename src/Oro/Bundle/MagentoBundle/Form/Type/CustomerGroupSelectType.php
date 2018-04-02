<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\CreateOrSelectInlineChannelAwareType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CustomerGroupSelectType extends AbstractType
{
    const NAME = 'oro_magento_customer_group_select';

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var bool */
    protected $canAssignChannel;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'magento_customer_group',
                'grid_name' => 'magento-customers-group-by-channel-grid',
                'configs' => [
                    'placeholder' => 'oro.magento.customergroup.placeholder'
                ]
            ]
        );

        // Set store form type readonly if ASSIGN permissions for integration not set
        $resolver->setNormalizer(
            'disabled',
            function (Options $options, $value) {
                return $this->isReadOnly() ? true : $value;
            }
        )->setNormalizer(
            'validation_groups',
            function (Options $options, $value) {
                return $options['disabled'] ? false : $value;
            }
        );
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
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CreateOrSelectInlineChannelAwareType::class;
    }

    /**
     * Checks if the form type should be read-only or not
     *
     * @return bool
     */
    protected function isReadOnly()
    {
        return !$this->authorizationChecker->isGranted('oro_integration_assign');
    }
}
