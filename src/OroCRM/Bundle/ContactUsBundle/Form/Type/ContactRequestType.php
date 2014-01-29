<?php
namespace OroCRM\Bundle\ContactUsBundle\Form\Type;

use Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactRequestType extends AbstractType implements EmbeddedFormInterface
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
            ->add('channel', $options['channel_form_type'], [
                    'class' => 'OroIntegrationBundle:Channel',
                    'property' => 'name',
                ])
            ->add('Submit', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest',
                'channel_form_type' => 'entity'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultCss()
    {
        return <<<CSS
form {
  font-family: "Helvetica Neue", Arial, Helvetica, sans-serif;
}

label {
  display: block;
  margin-bottom: 5px;
  cursor: pointer;
}

label, input, button, select, textarea {
font-size: 13px;
font-weight: normal;
line-height: 20px;
}

textarea, input[type="text"] {
background-color: #fff;
border: 1px solid #ccc;
}

label.validation-error {
  color: #C81717 !important;
}

.validation-error .error {
  border: 1px solid #e9322d;
  outline: 0;
  outline: thin dotted \9;
  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075), 0 0 8px rgba(211,33,33,0.6);
  -moz-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075), 0 0 8px rgba(211,33,33,0.6);
  box-shadow: inset 0 1px 1px rgba(0,0,0,0.075), 0 0 8px rgba(211,33,33,0.6);
  color: #555;
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


}
