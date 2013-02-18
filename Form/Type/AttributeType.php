<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\ImageType;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\FileType;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\BooleanType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleSelectType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleRadioType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiSelectType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiCheckboxType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\DateType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MetricType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MoneyType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\UrlType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\NumberType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MailType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\IntegerType;
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

    /**
     * @var boolean
     */
    protected $isEditing;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->isEditing = ($builder->getData()->getId() !== null);

        $this->addFieldId($builder);

        $this->addFieldCode($builder);

        $this->addFieldAttributeType($builder);

        $this->addFieldRequired($builder);

        $this->addFieldUnique($builder);

        $this->addFieldTranslatable($builder);

        $this->addFieldScopable($builder);

        $this->addFieldSearchable($builder);

        $this->addFieldDefaultValue($builder);

        if ($this->isEditing && $builder->getData()->getBackendType() === AbstractAttributeType::BACKEND_TYPE_OPTION) {
            $this->addFieldOptions($builder);
        }
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
        $options = array(
            'required'  => true,
            'disabled'  => $this->isEditing,
            'read_only' => $this->isEditing
        );
        $builder->add('code', 'text', $options);
    }

    /**
     * Add field frontend type to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldAttributeType(FormBuilderInterface $builder)
    {
        $options = array(
            'choices'   => $this->getAttributeTypeChoices(),
            'disabled'  => $this->isEditing,
            'read_only' => $this->isEditing
        );
        $builder->add('attributeType', 'choice', $options);
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
        $builder->add(
            'options',
            'collection',
            array(
                'type'         => new AttributeOptionType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false
            )
        );
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
    public function getAttributeTypeChoices()
    {
        $availablesTypes = array(
            new BooleanType(),
            new DateType(),
            new FileType(),
            new ImageType(),
            new IntegerType(),
            new MailType(),
            new MetricType(),
            new MoneyType(),
            new OptionMultiCheckboxType(),
            new OptionMultiSelectType(),
            new OptionSimpleRadioType(),
            new OptionSimpleSelectType(),
            new NumberType(),
            new TextAreaType(),
            new TextType(),
            new UrlType()
        );
        $types = array();
        foreach ($availablesTypes as $type) {
            $types[get_class($type)]= $type->getName();
        }
        asort($types);

        return $types;
    }
}
