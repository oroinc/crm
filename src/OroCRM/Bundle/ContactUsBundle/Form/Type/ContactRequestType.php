<?php
namespace OroCRM\Bundle\ContactUsBundle\Form\Type;

use Oro\Bundle\EmbeddedFormBundle\Form\Type\CustomLayoutFormTypeInterface;
use Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactRequestType extends AbstractType implements EmbeddedFormInterface, CustomLayoutFormTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'contact_request';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')
            ->add('email')
            ->add('phone')
            ->add('comment', 'textarea')
            ->add('submit', 'submit');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_channel_aware_form';
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getDefaultCss()
    {
        return <<<CSS
ul, li {
     list-style: none;
     overflow: hidden;
     clear: both;
     margin: 0;
 }
.page-title {
    width: 100%;
    overflow: hidden;
    border-bottom: 1px solid #ccc;
    margin: 0 0 25px;
}
 .page-title h1{
     margin: 0;
     font-size: 20px;
     color: #0a263c;
 }

.fieldset {
    border: 1px solid #bbafa0;
    background: #fbfaf6;
    padding: 22px 25px 12px 33px;
    margin: 28px 0;
}

.fieldset .legend {
    float: left;
    font-size: 13px;
    line-height: 20px;
    border: 1px solid #f19900;
    background: #f9f3e3;
    color: #e76200;
    margin: -33px 0 0 -10px;
    padding: 0 8px;
    position: relative;
}

.form-list .control-group {
    margin-bottom: 0px;
}

.form-list li.fields .control-group {
    float: left;
    width: 275px;
}

.form-list label {
    float: left;
    color: #666;
    font-weight: bold;
    position: relative;
    z-index: 0;
    margin-bottom: 0;
}
.form-list .controls {
    display: block;
    clear: both;
    width: 260px;
}

.form-list input[type="text"],
.form-list input[type="email"] {
    width: 254px;
    padding: 3px;
    margin-bottom: 3px;

}

.form-list li.wide .controls {
    width: 550px;
}

.form-list li.wide textarea {
    width: 100%;
    padding: 3px;
    margin-bottom: 3px;
}

.buttons-set {
    clear: both;
    margin: 4em 0 0;
    padding: 8px 10px 0;
    border-top: 1px solid #e4e4e4;
    text-align: right;
}

.buttons-set p.required {
    margin: 0 0 10px;
    font-size: 11px;
    text-align: right;
    color: #EB340A;
}

.buttons-set button {
    float: right;
    margin-left: 5px;
    display: block;
    height: 19px;
    border: 1px solid #de5400;
    background: #f18200;
    padding: 0 8px;
    font: bold 12px/19px Arial, Helvetica, sans-serif;
    text-align: center;
    white-space: nowrap;
    color: #fff;
    box-sizing: content-box;
}
CSS;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSuccessMessage()
    {
        return '<h3>Form has been submitted successfully</h3>{back_link}';
    }

    /**
     * {@inheritdoc}
     */
    public function geFormLayout()
    {
        return 'OroCRMContactUsBundle::form.html.twig';
    }
}
