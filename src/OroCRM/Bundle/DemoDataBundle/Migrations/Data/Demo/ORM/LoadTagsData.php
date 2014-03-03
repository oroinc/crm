<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class LoadTagsData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Account[]
     */
    protected $accountsRepository;

    /**
     * @var User[]
     */
    protected $usersRepository;

    /**
     * @var Contact[]
     */
    protected $contactsRepository;

    /** @var  TagManager */
    protected $tagManager;

    /** @var EntityRepository */
    protected $tagsRepository;

    /** @var Tag[] */
    protected $tagsUser;
    protected $randomUser;
    protected $randomUserTag;

    /** @var Tag[] */
    protected $tagsAccount;
    protected $randomAccountTag;

    /** @var  EntityManager */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadAccountData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData'
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
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadUsersTags();
        $this->loadAccountsTags();
        $this->loadContactsTags();
    }

    /**
     * @param ObjectManager $manager
     */
    protected function initSupportingEntities(ObjectManager $manager = null)
    {

        if ($manager) {
            $this->em = $manager;
        } else {
            $this->em = $this->container->get('doctrine.orm.entity_manager');
        }

        $this->usersRepository = $this->em->getRepository('OroUserBundle:User')->findAll();
        $this->accountsRepository = $this->em->getRepository('OroCRMAccountBundle:Account')->findAll();
        $this->contactsRepository = $this->em->getRepository('OroCRMContactBundle:Contact')->findAll();
        $this->tagsRepository = $this->em->getRepository('OroTagBundle:Tag')->findAll();

        $this->randomUser = count($this->usersRepository)-1;
        $this->randomUserTag = count($this->tagsUser)-1;
        $this->tagManager = $this->container->get('oro_tag.tag.manager');
    }

    /**
     * @param $tagsNames
     * @return Tag[]
     */
    protected function createTags($tagsNames)
    {
        $tags = array();
        foreach ($tagsNames as $tagName) {
            $tags[] = new Tag($tagName);
        }

        return $tags;
    }

    public function loadUsersTags()
    {
        $this->tagsUser = $this->createTags(array('Friends', 'Developer', 'Wholesale'));
        foreach ($this->usersRepository as $user) {
            $securityContext = $this->container->get('security.context');
            $token = new UsernamePasswordToken($user, $user->getUsername(), 'main');
            $securityContext->setToken($token);

            $ownTag = array($this->tagsUser[rand(0, $this->randomUserTag)]);
            $user->setTags(
                array(
                    'owner' => $ownTag,
                    'all' => array()
                )
            );
            $this->persist($this->em, $user);
            $this->tagManager->saveTagging($user, false);
        }
        $this->flush($this->em);
    }

    public function loadAccountsTags()
    {
        $this->tagsAccount = $this->createTags(
            array(
                'Commercial',
                'Business',
                'Vendor',
                'Gold Partner',
                'Service',
                '#new',
                '#vip',
                '#popular',
                '#call',
                '#discontinued',
                'Premium')
        );
        $this->randomAccountTag = count($this->tagsAccount)-1;

        foreach ($this->accountsRepository as $account) {
            $user = $this->usersRepository[rand(0, $this->randomUser)];

            $securityContext = $this->container->get('security.context');
            $token = new UsernamePasswordToken($user, $user->getUsername(), 'main');
            $securityContext->setToken($token);

            $ownTags = array(
                $this->tagsUser[rand(0, $this->randomUserTag)],
                $this->tagsAccount[rand(0, $this->randomAccountTag)]
            );

            $account->setTags(
                array(
                    'owner' => $ownTags ,
                    'all' => array()
                )
            );
            $this->persist($this->em, $account);
            $this->tagManager->saveTagging($account, false);
        }
        $this->flush($this->em);
        $this->em->clear('Oro\\Bundle\\AccountBundle\\Entity\\Account');
    }

    public function loadContactsTags()
    {
        foreach ($this->contactsRepository as $contact) {
            $user = $this->usersRepository[rand(0, $this->randomUser)];

            $securityContext = $this->container->get('security.context');
            $token = new UsernamePasswordToken($user, $user->getUsername(), 'main');
            $securityContext->setToken($token);

            $ownTags = array(
                $this->tagsUser[rand(0, $this->randomUserTag)],
                $this->tagsAccount[rand(0, $this->randomAccountTag-1)]
            );

            $contact->setTags(
                array(
                    'owner' => $ownTags,
                    'all' => array()
                )
            );
            $this->persist($this->em, $contact);
            $this->tagManager->saveTagging($contact, false);
        }
        $this->flush($this->em);
        $this->em->clear('Oro\\Bundle\\ContactBundle\\Entity\\Contact');
    }

    /**
     * Persist object
     *
     * @param mixed $manager
     * @param mixed $object
     */
    private function persist($manager, $object)
    {
        $manager->persist($object);
    }

    /**
     * Flush objects
     *
     * @param mixed $manager
     */
    private function flush($manager)
    {
        $manager->flush();
    }
}
