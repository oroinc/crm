<?php

namespace Oro\Bundle\AccountBundle\Form\Type;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\AccountBundle\Entity\Account;

class AccountType extends AbstractType
{
    /** @var RouterInterface */
    protected $router;

    /** @var EntityNameResolver */
    protected $entityNameResolver;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param RouterInterface               $router
     * @param EntityNameResolver            $entityNameResolver
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        RouterInterface $router,
        EntityNameResolver $entityNameResolver,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->router = $router;
        $this->entityNameResolver = $entityNameResolver;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // name
        $builder->add(
            'name',
            'text',
            array(
                'label' => 'oro.account.name.label',
                'required' => true,
            )
        );

        if ($this->authorizationChecker->isGranted('oro_contact_view')) {
            $builder->add(
                'default_contact',
                'oro_entity_identifier',
                array(
                    'class'    => 'OroContactBundle:Contact',
                    'multiple' => false
                )
            );

            // contacts
            $builder->add(
                'contacts',
                'oro_multiple_entity',
                array(
                    'add_acl_resource'      => 'oro_contact_view',
                    'class'                 => 'OroContactBundle:Contact',
                    'default_element'       => 'default_contact',
                    'required'              => false,
                    'selector_window_title' => 'oro.account.form.select_contacts',
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($this->authorizationChecker->isGranted('oro_contact_view')) {
            /** @var Account $account */
            $account = $form->getData();
            $view->children['contacts']->vars['grid_url']
                = $this->router->generate('oro_account_widget_contacts_info', array('id' => $account->getId()));
            $defaultContactId = $account->getDefaultContact() ? $account->getDefaultContact()->getId() : null;
            $view->children['contacts']->vars['initial_elements']
                = $this->getInitialElements($account->getContacts(), $defaultContactId);
        }
    }

    /**
     * @param Collection $contacts
     * @param int|null $default
     * @return array
     */
    protected function getInitialElements(Collection $contacts, $default)
    {
        $result = array();
        /** @var Contact $contact */
        foreach ($contacts as $contact) {
            if (!$contact->getId()) {
                continue;
            }
            $primaryPhone = $contact->getPrimaryPhone();
            $primaryEmail = $contact->getPrimaryEmail();
            $result[] = array(
                'id' => $contact->getId(),
                'label' => $this->entityNameResolver->getName($contact),
                'link' => $this->router->generate('oro_contact_info', array('id' => $contact->getId())),
                'extraData' => array(
                    array('label' => 'Phone', 'value' => $primaryPhone ? $primaryPhone->getPhone() : null),
                    array('label' => 'Email', 'value' => $primaryEmail ? $primaryEmail->getEmail() : null),
                ),
                'isDefault' => $default == $contact->getId()
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\AccountBundle\Entity\Account',
                'intention' => 'account',
            )
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
        return 'oro_account';
    }
}
