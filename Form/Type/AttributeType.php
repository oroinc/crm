<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Type for attribute form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class AttributeType extends AbstractType
{

    const FRONTEND_TYPE_TEXTFIELD = 'Text Field';
    const FRONTEND_TYPE_TEXTAREA  = 'Text Area';
    const FRONTEND_TYPE_PRICE     = 'Price';
    const FRONTEND_TYPE_DATE      = 'Date';
    const FRONTEND_TYPE_LIST      = 'List';
    const FRONTEND_TYPE_IMAGE     = 'Image';
    const FRONTEND_TYPE_FILE      = 'File';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addFieldId($builder);

        $this->addFieldCode($builder);

        $this->addFieldFrontendType($builder);

        $this->addFieldRequired($builder);

        $this->addFieldUnique($builder);

        $this->addFieldTranslatable($builder);

        $this->addFieldScopable($builder);

        $this->addFieldSearchable($builder);

        $this->addFieldDefaultValue($builder);

        $this->addFieldOptions($builder);
    }

    /**
     * Add field id to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldId(FormBuilderInterface $builder)
    {
        $builder->add('id', 'hidden');
    }

    /**
     * Add field code to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldCode(FormBuilderInterface $builder)
    {
        $options = array('required' => true, 'read_only' => $builder->getData()->getId());
        $builder->add('code', 'text', $options);
    }

    /**
     * Add field frontend type to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldFrontendType(FormBuilderInterface $builder)
    {
        $options = array(
            'choices'  => $this->getFrontendTypeChoices(),
            'read_only' => $builder->getData()->getId()
        );
        $builder->add('frontend_type', 'choice', $options);
    }

    /**
     * Add field required to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldRequired(FormBuilderInterface $builder)
    {
        $builder->add('required', 'checkbox', array('required' => false));
    }

    /**
     * Add field unique to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldUnique(FormBuilderInterface $builder)
    {
        $builder->add('unique', 'checkbox', array('required' => false));
    }

    /**
     * Add field default value to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldDefaultValue(FormBuilderInterface $builder)
    {
        $builder->add('default_value', 'text', array('required' => false));
    }

    /**
     * Add field searchable to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldSearchable(FormBuilderInterface $builder)
    {
        $builder->add('searchable', 'checkbox', array('required' => false));
    }

    /**
     * Add field translatable to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldTranslatable(FormBuilderInterface $builder)
    {
        $builder->add('translatable', 'checkbox', array('required' => false));
    }

    /**
     * Add field scopable to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldScopable(FormBuilderInterface $builder)
    {
        $builder->add('scopable', 'checkbox', array('required' => false));
    }

    /**
     * Add option fields to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldOptions(FormBuilderInterface $builder)
    {
        /*
        $builder->add(
            'options', 'collection', array(
                'type'         => new AttributeOptionType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false
            )
        );
        */
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\FlexibleEntityBundle\Entity\Attribute'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_attribute';
    }

    /**
     * Return available frontend type
     *
     * @return array
     */
    public function getFrontendTypeChoices()
    {
        return array(
            AbstractAttributeType::FRONTEND_TYPE_TEXTFIELD => AbstractAttributeType::FRONTEND_TYPE_TEXTFIELD,
            AbstractAttributeType::FRONTEND_TYPE_TEXTAREA => AbstractAttributeType::FRONTEND_TYPE_TEXTAREA,
            AbstractAttributeType::FRONTEND_TYPE_PRICE => AbstractAttributeType::FRONTEND_TYPE_PRICE,
            AbstractAttributeType::FRONTEND_TYPE_DATE => AbstractAttributeType::FRONTEND_TYPE_DATE,
            AbstractAttributeType::FRONTEND_TYPE_DATETIME => AbstractAttributeType::FRONTEND_TYPE_DATETIME,
            AbstractAttributeType::FRONTEND_TYPE_LIST => AbstractAttributeType::FRONTEND_TYPE_LIST,
            AbstractAttributeType::FRONTEND_TYPE_MULTILIST => AbstractAttributeType::FRONTEND_TYPE_LIST,
            //                     'textfield' => AbstractAttributeType::FRONTEND_TYPE_IMAGE,
            //                     'textfield' => AbstractAttributeType::FRONTEND_TYPE_FILE
        );
    }
}
