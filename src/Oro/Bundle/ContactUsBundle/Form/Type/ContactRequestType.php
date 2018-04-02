<?php
namespace Oro\Bundle\ContactUsBundle\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactRequestType extends AbstractType implements EmbeddedFormInterface
{
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
        return 'oro_contactus_contact_request';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['dataChannelField']) {
            $builder->add(
                'dataChannel',
                ChannelSelectType::class,
                [
                    'required' => true,
                    'label' => 'oro.contactus.contactrequest.data_channel.label',
                    'entities' => [
                        'Oro\\Bundle\\ContactUsBundle\\Entity\\ContactRequest'
                    ],
                ]
            );
        }

        $builder->add(
            'firstName',
            TextType::class,
            ['required' => true, 'label' => 'oro.contactus.contactrequest.first_name.label']
        );
        $builder->add(
            'lastName',
            TextType::class,
            ['required' => true, 'label' => 'oro.contactus.contactrequest.last_name.label']
        );
        $builder->add(
            'emailAddress',
            TextType::class,
            ['required' => false, 'label' => 'oro.contactus.contactrequest.email_address.label']
        );
        $builder->add('phone', TextType::class, [
            'required' => false,
            'label' => 'oro.contactus.contactrequest.phone.label'
        ]);
        $builder->add('comment', TextareaType::class, ['label' => 'oro.contactus.contactrequest.comment.label']);
        $builder->add('submit', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            'data_class' => 'Oro\Bundle\ContactUsBundle\Entity\ContactRequest',
            'dataChannelField' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getDefaultCss()
    {
        return <<<CSS
body {
    margin: 0;
    color: #000;
    min-width: 320px;
    background: #fff;
    font: 13px/18px Arial, Helvetica, sans-serif;
}

#page div {
    box-sizing: content-box;
    -moz-box-sizing: content-box;
    -webkit-box-sizing: content-box;
}

#page {
    padding: 0 40px;
}

.row-group {
    width: 100%;
}

.row-group:after {
    content: "";
    display: block;
    clear: both;
}

.row-group label {
    display: block;
    clear: both;
    font-weight: normal;
    margin: 0 0 3px;
}

.row-group label em,
.control-group label em {
    color: #f00;
    font-size: 16px;
}

.row-group .box {
    display: inline-block;
    width: 48.5%;
    min-width: 410px;
    margin: 0 0 5px;
}

.row-group .box:first-child {
    padding-right: 2%;
}

.row-group input[type="text"],
.row-group textarea,
.row-group button {
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 3px;
    -webkit-border-radius: 3px;
    font-size: 12px;
    color: #000;
    background-color: #fff;
}

.row-group input[type="text"] {
    display: block;
    width: 100%;
    height: 26px;
    line-height: 26px;
    padding: 0 10px;
}

.row-group textarea {
    font-size: 12px;
    display: block;
    min-width: 410px;
    width: 99.5%;
    min-height: 75px;
    resize: vertical;
}

.row-group button {
    font: 13px/24px Arial, Helvetica, sans-serif;
    height: 28px;
    background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#ffffff), to(#f1f0f0));
    background: -webkit-linear-gradient(top, #ffffff, #f1f0f0);
    background: -moz-linear-gradient(top, #ffffff, #f1f0f0);
    background: -ms-linear-gradient(top, #ffffff, #f1f0f0);
    background: -o-linear-gradient(top, #ffffff, #f1f0f0);
    padding: 0 25px;
    margin-top: 10px;
}
span.validation-failed {
    color: #c81717;
    display: block;
    line-height: 1.1em;
    margin: 3px 0 6px 0;
}
CSS;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSuccessMessage()
    {
        return '<p>Form has been submitted successfully</p>{back_link}';
    }
}
