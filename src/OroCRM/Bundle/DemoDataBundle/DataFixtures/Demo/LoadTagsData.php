<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class LoadTagsData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Account[]
     */
    protected $accounts;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var User[]
     */
    protected $users;

    /**
     * @var Contact[]
     */
    protected $contacts;

    /**
     * @var EntityManager
     */
    protected $contactManager;

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

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->userManager = $container->get('oro_user.manager');
        $this->tagManager = $container->get('oro_tag.tag.manager');
        $this->contactManager = $container->get('doctrine.orm.entity_manager');
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
    protected function initSupportingEntities(ObjectManager $manager)
    {
        $this->users = $manager->getRepository('OroUserBundle:User')->findAll();
        $this->randomUser = count($this->users)-1;

        $this->accounts = $manager->getRepository('OroCRMAccountBundle:Account')->findAll();
        $this->contacts = $manager->getRepository('OroCRMContactBundle:Contact')->findAll();

        $this->tagsRepository = $manager->getRepository('OroTagBundle:Tag');
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
        $this->randomUserTag = count($this->tagsUser)-1;
        foreach ($this->users as $user) {
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
            $this->persist($this->userManager->getStorageManager(), $user);
            $this->tagManager->saveTagging($user, false);
        }
        $this->flush($this->userManager->getStorageManager());
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
        $this->randomUser = count($this->users)-1;
        foreach ($this->accounts as $account) {
            $user = $this->users[rand(0, $this->randomUser)];

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
            $this->persist($this->contactManager, $account);
            $this->tagManager->saveTagging($account, false);
        }
        $this->flush($this->contactManager);
    }

    public function loadContactsTags()
    {
        foreach ($this->contacts as $contact) {
            $user = $this->users[rand(0, $this->randomUser)];

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
            $this->persist($this->contactManager, $contact);
            $this->tagManager->saveTagging($contact, false);
        }
        $this->flush($this->contactManager);
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

    public function getOrder()
    {
        return 300;
    }
}
