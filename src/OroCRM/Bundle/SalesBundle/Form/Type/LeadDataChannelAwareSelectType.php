<?php
namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeadDataChannelAwareSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_sales_lead_data_channel_aware_select';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline_channel_aware';
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['grid_view_widget_route'] = $options['grid_view_widget_route'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias'           => 'leads',
                'create_form_route'            => 'orocrm_sales_lead_data_channel_aware_create',
                'grid_view_widget_route'       => 'orocrm_sales_datagrid_lead_datachannel_aware',
                'configs'                      => [
                    'placeholder' => 'orocrm.sales.form.choose_lead'
                ],
                'channel_field'                => 'dataChannel',
                'channel_required'             => true,
                'existing_entity_grid_id'      => 'id',
                'create_enabled'               => true,
                'create_acl'                   => null,
                'create_form_route_parameters' => [],
                'grid_widget_route'            => 'oro_datagrid_widget',
                'grid_name'                    => null,
                'grid_parameters'              => [],
                'grid_render_parameters'       => []
            ]
        );
    }
}
