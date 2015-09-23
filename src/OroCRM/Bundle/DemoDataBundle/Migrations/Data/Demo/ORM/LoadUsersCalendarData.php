<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Entity\CalendarProperty;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarRepository;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;

class LoadUsersCalendarData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var User[] */
    private $users;

    /** @var EntityRepository */
    protected $user;

    /** @var CalendarRepository */
    protected $calendar;

    /** @var Organization */
    protected $organization;

    /** @var EntityManager */
    protected $em;

    /** @var SecurityContext */
    protected $securityContext;

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

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->em              = $container->get('doctrine')->getManager();
        $this->securityContext = $container->get('security.context');

        $this->user     = $this->em->getRepository('OroUserBundle:User');
        $this->calendar = $this->em->getRepository('OroCalendarBundle:Calendar');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->users = $this->user->findAll();
        $this->organization = $this->getReference('default_organization');

        $this->loadCalendars();
        $this->connectCalendars();
    }

    protected function loadCalendars()
    {
        $days   = $this->getDatePeriod();
        $events = [];
        foreach ($days as $day) {
            /** @var \DateTime $day */
            if (!$this->isWeekend($day)) {
                //work day
                $event = new CalendarEvent();
                $event->setTitle('Work Reminder');
                $day->setTime(8, 0, 0);
                $event->setStart(clone $day);
                $day->setTime(18, 0, 0);
                $event->setEnd(clone $day);
                $event->setAllDay(true);

                $events['workday'][] = $event;

                //call
                $event = new CalendarEvent();
                $event->setTitle('Client Call');
                $day->setTime(11, 0, 0);
                $event->setStart(clone $day);
                $day->setTime(12, 0, 0);
                $event->setEnd(clone $day);
                $event->setAllDay(false);

                $events['call'][] = $event;

                //meeting
                $event = new CalendarEvent();
                $event->setTitle('Meeting');
                $day->setTime(16, 0, 0);
                $event->setStart(clone $day);
                $day->setTime(18, 0, 0);
                $event->setEnd(clone $day);
                $event->setAllDay(false);

                $events['meeting'][] = $event;

                //lunch
                $event = new CalendarEvent();
                $event->setTitle('Lunch');
                $day->setTime(12, 0, 0);
                $event->setStart(clone $day);
                $day->setTime(12, 30, 0);
                $event->setEnd(clone $day);
                $event->setAllDay(false);

                $events['lunch'][] = $event;

                //business trip
                $event = new CalendarEvent();
                $event->setTitle('Business trip');
                $day->setTime(0, 0, 0);
                $event->setStart(clone $day);
                $day->setTime(0, 0, 0);
                $day->add(\DateInterval::createFromDateString('+3 days'));
                $event->setEnd(clone $day);
                $event->setAllDay(true);

                $events['b_trip'][] = $event;
            } else {
                $event = new CalendarEvent();
                $event->setTitle('Weekend');
                $day->setTime(8, 0, 0);
                $event->setStart(clone $day);
                $day->setTime(18, 0, 0);
                $event->setEnd(clone $day);
                $event->setAllDay(true);

                $events['weekend'][] = $event;
            }
        }

        foreach ($this->users as $user) {
            //get default calendar, each user has default calendar after creation
            $calendar = $this->calendar->findDefaultCalendar($user->getId(), $this->organization->getId());
            $this->setSecurityContext($calendar->getOwner());
            foreach ($events as $typeEvents) {
                if (mt_rand(0, 1)) {
                    foreach ($typeEvents as $typeEvent) {
                        $calendar->addEvent(clone $typeEvent);
                    }
                }
            }

            $this->em->persist($calendar);
        }

        $this->em->flush();
        $this->em->clear('Oro\Bundle\CalendarBundle\Entity\CalendarEvent');
        $this->em->clear('Oro\Bundle\CalendarBundle\Entity\Calendar');
    }

    protected function connectCalendars()
    {
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

        /** @var User[] $users */
        $users = $this->getRandomUsers();

        foreach ($users as $user) {
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

                $this->em->persist($calendarProperty);
            }

            if (mt_rand(0, 1)) {
                $calendarProperty = new CalendarProperty();
                $calendarProperty
                    ->setTargetCalendar($calendarSale)
                    ->setCalendarAlias('user')
                    ->setCalendar($calendar->getId());

                $this->em->persist($calendarProperty);
            }

            if (mt_rand(0, 1)) {
                $calendarProperty = new CalendarProperty();
                $calendarProperty
                    ->setTargetCalendar($calendarMarket)
                    ->setCalendarAlias('user')
                    ->setCalendar($calendar->getId());

                $this->em->persist($calendarProperty);
            }

            $this->em->persist($calendar);
        }

        $this->em->flush();
    }

    /**
     * @param int $limit
     *
     * @return User[]
     */
    protected function getRandomUsers($limit = 5)
    {
        $userIds = $this->user->createQueryBuilder('u')
            ->select('u.id')
            ->getQuery()
            ->getScalarResult();

        if (count($userIds) > $limit) {
            $rawList = [];
            foreach ($userIds as $key => $value) {
                // due array_rand() will pick only keywords
                $rawList[$value['id']] = null;
            }

            $keyList = array_rand($rawList, $limit);

            $criteria = new Criteria();
            $criteria->where(Criteria::expr()->in('id', $keyList));

            $result = $this->user->createQueryBuilder('u')
                ->addCriteria($criteria)
                ->getQuery()
                ->getResult();
        } else {
            $result = $this->users;
        }

        return $result;
    }

    /**
     * @return \DatePeriod
     */
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
    protected function setSecurityContext(User $user)
    {
        $token = new UsernamePasswordOrganizationToken($user, $user->getUsername(), 'main', $this->organization);
        $this->securityContext->setToken($token);
    }
}
