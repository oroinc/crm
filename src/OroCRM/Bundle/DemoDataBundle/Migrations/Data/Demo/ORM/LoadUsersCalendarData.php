<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Entity\CalendarProperty;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarRepository;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;

class LoadUsersCalendarData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var EntityRepository */
    protected $user;

    /** @var CalendarRepository */
    protected $calendar;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUserData'
        ];
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        /** @var  EntityManager $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->user = $entityManager->getRepository('OroUserBundle:User');
        $this->calendar = $entityManager->getRepository('OroCalendarBundle:Calendar');
    }

    public function load(ObjectManager $manager)
    {
        $this->loadCalendars();
        $this->connectCalendars();
    }

    protected function loadCalendars()
    {
        /** @var \Oro\Bundle\OrganizationBundle\Entity\Organization $organization */
        $organization = $this->getReference('default_organization');
        /** @var \Oro\Bundle\UserBundle\Entity\User[] $users */
        $users = $this->user->findAll();
        foreach ($users as $user) {
            //get default calendar, each user has default calendar after creation
            $calendar = $this->calendar->findDefaultCalendar($user->getId(), $organization->getId());
            $this->setSecurityContext($calendar->getOwner());
            /** @var CalendarEvent $event */
            $days = $this->getDatePeriod();
            foreach ($days as $day) {
                /** @var \DateTime $day */
                if (!$this->isWeekend($day)) {
                    //work day
                    if (mt_rand(0, 1)) {
                        $event = new CalendarEvent();
                        $event->setTitle('Work Reminder');
                        $day->setTime(8, 0, 0);
                        $event->setStart(clone $day);
                        $day->setTime(18, 0, 0);
                        $event->setEnd(clone $day);
                        $event->setAllDay(true);
                        $calendar->addEvent($event);
                    }
                    //call
                    if (mt_rand(0, 1)) {
                        $event = new CalendarEvent();
                        $event->setTitle('Client Call');
                        $day->setTime(11, 0, 0);
                        $event->setStart(clone $day);
                        $day->setTime(12, 0, 0);
                        $event->setEnd(clone $day);
                        $event->setAllDay(false);
                        $calendar->addEvent($event);
                    }
                    //meeting
                    if (mt_rand(0, 1)) {
                        $event = new CalendarEvent();
                        $event->setTitle('Meeting');
                        $day->setTime(16, 0, 0);
                        $event->setStart(clone $day);
                        $day->setTime(18, 0, 0);
                        $event->setEnd(clone $day);
                        $event->setAllDay(false);
                        $calendar->addEvent($event);
                    }
                    //lunch
                    if (mt_rand(0, 1)) {
                        $event = new CalendarEvent();
                        $event->setTitle('Lunch');
                        $day->setTime(12, 0, 0);
                        $event->setStart(clone $day);
                        $day->setTime(12, 30, 0);
                        $event->setEnd(clone $day);
                        $event->setAllDay(false);
                        $calendar->addEvent($event);
                    }
                    //business trip
                    if (mt_rand(0, 1)) {
                        $event = new CalendarEvent();
                        $event->setTitle('Business trip');
                        $day->setTime(0, 0, 0);
                        $event->setStart(clone $day);
                        $day->setTime(0, 0, 0);
                        $day->add(\DateInterval::createFromDateString('+3 days'));
                        $event->setEnd(clone $day);
                        $event->setAllDay(true);
                        $calendar->addEvent($event);
                    }
                } else {
                    $event = new CalendarEvent();
                    $event->setTitle('Weekend');
                    $day->setTime(8, 0, 0);
                    $event->setStart(clone $day);
                    $day->setTime(18, 0, 0);
                    $event->setEnd(clone $day);
                    $event->setAllDay(true);
                    $calendar->addEvent($event);
                }
            }
            $this->persist($this->container->get('doctrine.orm.entity_manager'), $calendar);
        }
        $this->flush($this->container->get('doctrine.orm.entity_manager'));
    }

    protected function connectCalendars()
    {
        /** @var \Oro\Bundle\UserBundle\Entity\User[] $users */
        $users = $this->user->findAll();
        // first user is admin, often
        /** @var \Oro\Bundle\UserBundle\Entity\User $admin */
        $admin = $this->user->find(1);
        /** @var Calendar $calendarAdmin */
        $calendarAdmin = $this->calendar->findDefaultCalendar($admin->getId(), $admin->getOrganization()->getId());

        /** @var \Oro\Bundle\UserBundle\Entity\User $sale */
        $sale = $this->user->findOneBy(['username' => 'sale']);
        /** @var Calendar $calendarSale */
        $calendarSale = $this->calendar->findDefaultCalendar($sale->getId(), $sale->getOrganization()->getId());

        /** @var \Oro\Bundle\UserBundle\Entity\User $market */
        $market = $this->user->findOneBy(['username' => 'marketing']);
        /** @var Calendar $calendarMarket */
        $calendarMarket = $this->calendar->findDefaultCalendar($market->getId(), $market->getOrganization()->getId());

        $i = 0;
        while ($i <= 5) {
            //get random user
            $userId = mt_rand(2, count($users)-1);
            $user = $users[$userId];
            unset($users[$userId]);
            $users = array_values($users);
            if (in_array($user->getId(), [$admin->getId(), $sale->getId(), $market->getId()])) {
                //to prevent self assignment
                continue;
            }
            /** @var Calendar $calendar */
            $calendar = $this->calendar->findDefaultCalendar($user->getId(), $user->getOrganization()->getId());

            if (mt_rand(0, 1)) {
                $calendarProperty = new CalendarProperty();
                $calendarProperty
                    ->setTargetCalendar($calendarAdmin)
                    ->setCalendarAlias('user')
                    ->setCalendar($calendar->getId());
                $this->persist($this->container->get('doctrine.orm.entity_manager'), $calendarProperty);
            }

            if (mt_rand(0, 1)) {
                $calendarProperty = new CalendarProperty();
                $calendarProperty
                    ->setTargetCalendar($calendarSale)
                    ->setCalendarAlias('user')
                    ->setCalendar($calendar->getId());
                $this->persist($this->container->get('doctrine.orm.entity_manager'), $calendarProperty);
            }

            if (mt_rand(0, 1)) {
                $calendarProperty = new CalendarProperty();
                $calendarProperty
                    ->setTargetCalendar($calendarMarket)
                    ->setCalendarAlias('user')
                    ->setCalendar($calendar->getId());
                $this->persist($this->container->get('doctrine.orm.entity_manager'), $calendarProperty);
            }

            $this->persist($this->container->get('doctrine.orm.entity_manager'), $calendar);
            $i++;
        }
        $this->flush($this->container->get('doctrine.orm.entity_manager'));
    }

    protected function getDatePeriod()
    {
        $month = new \DatePeriod(
            new \DateTime('now'),
            \DateInterval::createFromDateString('+1 day'),
            new \DateTime('now +1 month'),
            \DatePeriod::EXCLUDE_START_DATE
        );
        return $month;
    }

    /**
     * @param \DateTime $date
     * @return bool
     */
    protected function isWeekend($date)
    {
        $day = date('w', $date->getTimestamp());
        if ($day == 0 || $day == 6) {
            return true;
        }
        return false;
    }

    /**
     * @param User $user
     */
    protected function setSecurityContext($user)
    {
        $securityContext = $this->container->get('security.context');
        $token = new UsernamePasswordOrganizationToken(
            $user,
            $user->getUsername(),
            'main',
            $this->getReference('default_organization')
        );
        $securityContext->setToken($token);
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
