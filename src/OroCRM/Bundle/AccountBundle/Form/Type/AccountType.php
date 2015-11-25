<?php

namespace OroCRM\Bundle\AccountBundle\Form\Type;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\Routing\Router;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\Entity\Account;

class AccountType extends AbstractType
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var boolean
     */
    private $canViewContact;

    /**
     * @param Router $router
     * @param EntityNameResolver $entityNameResolver
     * @param SecurityFacade $securityFacade
     */
    public function __construct(Router $router, EntityNameResolver $entityNameResolver, SecurityFacade $securityFacade)
    {
        $this->entityNameResolver = $entityNameResolver;
        $this->router             = $router;
        $this->securityFacade     = $securityFacade;
        $this->canViewContact     = $this->securityFacade->isGranted('orocrm_contact_view');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // name
        $builder->add(
            'name',
            'text',
            array(
                'label' => 'orocrm.account.name.label',
                'required' => true,
            )
        );

        if ($this->canViewContact) {
            $builder->add(
                'default_contact',
                'oro_entity_identifier',
                array(
                    'class'    => 'OroCRMContactBundle:Contact',
                    'multiple' => false
                )
            );

            // contacts
            $builder->add(
                'contacts',
                'oro_multiple_entity',
                array(
                    'add_acl_resource'      => 'orocrm_contact_view',
                    'class'                 => 'OroCRMContactBundle:Contact',
                    'default_element'       => 'default_contact',
                    'required'              => false,
                    'selector_window_title' => 'orocrm.account.form.select_contacts',
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($this->canViewContact) {
            /** @var Account $account */
            $account = $form->getData();
            $view->children['contacts']->vars['grid_url']
                = $this->router->generate('orocrm_account_widget_contacts_info', array('id' => $account->getId()));
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
        if ($this->canViewContact) {
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
                    'link' => $this->router->generate('orocrm_contact_info', array('id' => $contact->getId())),
                    'extraData' => array(
                        array('label' => 'Phone', 'value' => $primaryPhone ? $primaryPhone->getPhone() : null),
                        array('label' => 'Email', 'value' => $primaryEmail ? $primaryEmail->getEmail() : null),
                    ),
                    'isDefault' => $default == $contact->getId()
                );
            }
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
                'data_class' => 'OroCRM\Bundle\AccountBundle\Entity\Account',
                'intention' => 'account',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation' => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_account';
    }
}
