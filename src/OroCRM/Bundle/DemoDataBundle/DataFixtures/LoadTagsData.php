<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use OroCRM\Bundle\DemoDataBundle\DataFixtures\AbstractFlexibleFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\ContactBundle\Entity\Source;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class LoadTagsData extends AbstractFlexibleFixture implements ContainerAwareInterface, OrderedFixtureInterface
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
    protected $tags;


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
        $this->initSupportingEntities();
        $this->tags = $this->getTags();
        $this->loadUsersTags();
        $this->loadAccountsTags();
    }

    protected function initSupportingEntities()
    {
        $userStorageManager = $this->userManager->getStorageManager();
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $this->users = $userStorageManager->getRepository('OroUserBundle:User')->findAll();

        $this->accounts = $this->contactManager->getRepository('OroCRMAccountBundle:Account')->findAll();
        $this->contacts = $this->contactManager->getRepository('OroCRMContactBundle:Contact')->findAll();

        $this->tagsRepository = $entityManager->getRepository('OroTagBundle:Tag');
    }


    /**
     * @return Tag[]
     */
    protected function getTags()
    {
        $tags = $this->tagsRepository->findAll();
        $keys = array();
        foreach ($tags as $tag) {
            /** @var Tag $tag */
            $keys[] =  $tag->getName();
        }
        $tags = array_combine($keys, array_values($tags));
        return $tags;
    }

    /**
     * @param array $tagged
     * @return array
     */
    protected function getOwnTags(array $tagged)
    {
        $ownTags = array();
        foreach ($tagged as $taggedField) {
            if (array_key_exists($taggedField, $this->tags)) {
                $ownTags[$taggedField] = $this->tags[$taggedField];
            } else {
                $ownTags[$taggedField] = new Tag($taggedField);
                $this->tags[$taggedField] = $ownTags[$taggedField];
            }
        }
        return $ownTags;
    }

    public function loadUsersTags()
    {
        foreach ($this->users as $user) {
            $securityContext = $this->container->get('security.context');
            $token = new UsernamePasswordToken($user, '123123q', 'main');
            $securityContext->setToken($token);


            $ownTags = $this->getOwnTags(array($user->getFirstname(), $user->getLastname(), $user->getEmail()));

            $user->setTags(
                array(
                    'owner' => $ownTags ,
                    'all' => array()
                )
            );
            $this->persist($this->userManager->getStorageManager(), $user);
            $this->tagManager->saveTagging($user);
        }
        $this->flush($this->userManager->getStorageManager());
    }

    public function loadAccountsTags()
    {
        foreach ($this->accounts as $account) {
            $user = $this->users[rand(0, count($this->users)-1)];

            $securityContext = $this->container->get('security.context');
            $token = new UsernamePasswordToken($user, '123123q', 'main');
            $securityContext->setToken($token);

            $ownTags = $this->getOwnTags(
                array(
                    $account->getName(),
                    $account->getValue('website')->getVarchar(),
                    $account->getValue('email')->getVarchar()
                )
            );
            $account->setTags(
                array(
                    'owner' => $ownTags ,
                    'all' => array()
                )
            );
            $this->persist($this->contactManager, $account);
            $this->tagManager->saveTagging($account);
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
