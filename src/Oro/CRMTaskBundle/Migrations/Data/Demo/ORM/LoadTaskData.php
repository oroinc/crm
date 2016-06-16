<?php

namespace Oro\CRMTaskBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\TaskBundle\Entity\Task;
use OroCRM\Bundle\TaskBundle\Entity\TaskPriority;

class LoadTaskData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    const FIXTURES_COUNT = 20;

    /**
     * @var array
     */
    static protected $fixtureSubjects = [
        'Lorem ipsum dolor sit amet, consectetuer adipiscing elit',
        'Aenean commodo ligula eget dolor',
        'Aenean massa',
        'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus',
        'Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem',
        'Nulla consequat massa quis enim',
        'Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu',
        'In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo',
        'Nullam dictum felis eu pede mollis pretium',
        'Integer tincidunt',
        'Cras dapibus',
        'Vivamus elementum semper nisi',
        'Aenean vulputate eleifend tellus',
        'Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim',
        'Aliquam lorem ante, dapibus in, viverra quis, feugiat',
        'Aenean imperdiet. Etiam ultricies nisi vel',
        'Praesent adipiscing',
        'Integer ante arcu',
        'Curabitur ligula sapien',
        'Donec posuere vulputate'
    ];

    /**
     * @var array
     */
    static protected $fixtureDescriptions = [
        'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.',
        'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.',
        'Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim.',
        'Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet.',
        'Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi..',
        'Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra,',
        'Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel.',
        'Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus.',
        'Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed.',
        'Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus.',
        'Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus.',
        'Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales.',
        'Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus.',
        'Cras ultricies mi eu turpis hendrerit fringilla. Vestibulum ante ipsum primis in faucibus orci luctus.',
        'Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum.',
        'Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Praesent adipiscing.',
        'Vestibulum volutpat pretium libero. Cras id dui. Aenean ut eros et nisl sagittis vestibulum.',
        'Sed lectus. Donec mollis hendrerit risus. Phasellus nec sem in justo pellentesque facilisis. Etiam imperdiet.',
        'Phasellus leo dolor, tempus non, auctor et, hendrerit quis, nisi. Curabitur ligula sapien, tincidunt non.',
        'Praesent congue erat at massa. Sed cursus turpis vitae tortor. Donec posuere vulputate arcu.',
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadAccountData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $om)
    {
        $this->persistDemoTasks($om);

        $om->flush();
    }

    /**
     * @param ObjectManager $om
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function persistDemoTasks(ObjectManager $om)
    {
        $organization = $this->getReference('default_organization');

        $priorities = $om->getRepository('OroCRMTaskBundle:TaskPriority')->findAll();
        if (empty($priorities)) {
            return;
        }
        $users = $om->getRepository('OroUserBundle:User')->findAll();
        if (empty($users)) {
            return;
        }
        $accounts = $om->getRepository('OroCRMAccountBundle:Account')->findAll();
        $contacts = $om->getRepository('OroCRMContactBundle:Contact')->findAll();

        for ($i = 0; $i < self::FIXTURES_COUNT; ++$i) {
            /** @var User $assignedTo */
            $assignedTo = $this->getRandomEntity($users);
            /** @var TaskPriority $taskPriority */
            $taskPriority = $this->getRandomEntity($priorities);

            if ($om->getRepository('OroCRMTaskBundle:Task')->findOneBySubject(self::$fixtureSubjects[$i])) {
                // Task with this title is already exist
                continue;
            }

            $task = new Task();
            $task->setSubject(self::$fixtureSubjects[$i]);
            $task->setDescription(self::$fixtureDescriptions[$i]);
            $dueDate = new \DateTime();
            $dueDate->add(new \DateInterval(sprintf('P%dDT%dM', rand(0, 30), rand(0, 1440))));
            $task->setDueDate($dueDate);
            $task->setOwner($assignedTo);
            $task->setTaskPriority($taskPriority);
            $task->setOrganization($organization);

            $randomPath = rand(1, 10);

            if ($randomPath > 2) {
                $contact = $this->getRandomEntity($contacts);
                if ($contact) {
                    $this->addActivityTarget($task, $contact);
                }
            }

            if ($randomPath > 3) {
                $account = $this->getRandomEntity($accounts);
                if ($account) {
                    $this->addActivityTarget($task, $account);
                }
            }

            if ($randomPath > 4) {
                $user = $this->getRandomEntity($users);
                if ($user) {
                    $this->addActivityTarget($task, $user);
                }
            }

            $om->persist($task);
        }
    }

    /**
     * @param object[] $entities
     *
     * @return object|null
     */
    protected function getRandomEntity($entities)
    {
        if (empty($entities)) {
            return null;
        }

        return $entities[rand(0, count($entities) - 1)];
    }

    /**
     * @param Task   $task
     * @param object $target
     */
    protected function addActivityTarget(Task $task, $target)
    {
        if ($task->supportActivityTarget(get_class($target))) {
            $securityContext = $this->container->get('security.context');
            $user = $task->getOwner();
            $token = new UsernamePasswordOrganizationToken(
                $user,
                $user->getUsername(),
                'main',
                $this->getReference('default_organization')
            );
            $securityContext->setToken($token);
            $task->addActivityTarget($target);
        }
    }
}
