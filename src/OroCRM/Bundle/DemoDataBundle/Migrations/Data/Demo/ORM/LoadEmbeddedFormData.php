<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Builder\BuilderFactory;
use OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest;
use OroCRM\Bundle\ContactUsBundle\Form\Type\ContactRequestType;

class LoadEmbeddedFormData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var Organization */
    protected $organization;

    /** @var  Channel */
    protected $dataChannel;

    // @codingStandardsIgnoreStart
    protected $contactRequests = array(
        array(
            'firstName' => 'Jason',
            'lastName' => 'Mahler',
            'emailAddress' => 'Jason@test-email.com',
            'phone' => '3943948415',
            'comment' => 'I’m very interested in what you have to offer in your web store. I would love to find out more information',
            'contactReason' => 'Want to know more about the product',
        ),
        array(
            'firstName' => 'Thomas',
            'lastName' => 'Parker',
            'emailAddress' => 'Thomas@test-email.com',
            'phone' => '39448248415',
            'comment' => 'A potential partnership with your team is something we would love to pursue. Let’s set up a call for next week – I’m available M-W in the morning',
            'contactReason' => 'Interested in partnership',
        ),
        array(
            'firstName' => 'Elizabeth',
            'lastName' => 'Hick',
            'emailAddress' => 'Elizabeth@test-email.com',
            'phone' => '25448248415',
            'comment' => 'What does your team offer in the way of layout design for website building?',
            'contactReason' => 'Need help or assistance',
        ),
    );
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadBusinessUnitData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $this->organization = $this->getReference('default_organization');

        $this->createDataChannel($om);
        $this->persistDemoEmbeddedForm($om);
        $this->persistDemoContactUsForm($om);
        $om->flush();
    }

    /**
     * @param ObjectManager $om
     */
    public function createDataChannel(ObjectManager $om)
    {
        /** @var BuilderFactory $builderFactory */
        $builderFactory = $this->container->get('orocrm_channel.builder.factory');
        $this->dataChannel = $builderFactory
            ->createBuilder()
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setEntities(['OroCRM\\Bundle\\ContactUsBundle\\Entity\\ContactRequest'])
            ->setChannelType('custom')
            ->getChannel();
        $om->persist($this->dataChannel);
        $om->flush();
    }

    /**
     * @param ObjectManager $om
     */
    protected function persistDemoEmbeddedForm(
        ObjectManager $om
    ) {
        $embeddedForm = new EmbeddedForm();
        /** @var ContactRequestType $contactUs */
        $contactUs = $this->container->get('orocrm_contact_us.embedded_form');
        $embeddedForm->setFormType('orocrm_contact_us.embedded_form');
        $embeddedForm->setCss($contactUs->getDefaultCss());
        $embeddedForm->setSuccessMessage($contactUs->getDefaultSuccessMessage());
        $embeddedForm->setTitle('Contact Us Form');
        $embeddedForm->setOwner($this->organization);
        $embeddedForm->setDataChannel($this->dataChannel);
        $om->persist($embeddedForm);
    }

    /**
     * @param ObjectManager $om
     */
    protected function persistDemoContactUsForm(ObjectManager $om)
    {
        foreach ($this->contactRequests as $contactRequest) {
            $request = new ContactRequest();
            $contactRequest['contactReason'] = $om->getRepository('OroCRMContactUsBundle:ContactReason')
                ->findOneBy(array('label' => $contactRequest['contactReason']));
            foreach ($contactRequest as $property => $value) {
                call_user_func_array(array($request, 'set' . ucfirst($property)), array($value));
            }
            $request->setPreferredContactMethod(ContactRequest::CONTACT_METHOD_BOTH);
            $request->setCreatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
            $request->setOwner($this->organization);
            $request->setDataChannel($this->dataChannel);
            $om->persist($request);
        }
    }
}
