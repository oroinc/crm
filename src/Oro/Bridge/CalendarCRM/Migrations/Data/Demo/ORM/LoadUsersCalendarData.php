<?php

namespace Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Entity\CalendarProperty;
use Oro\Bundle\CalendarBundle\Entity\Recurrence;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarRepository;
use Oro\Bundle\CalendarBundle\Model;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Loads user calendars
 */
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

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var \DateTimeZone */
    protected $timeZone;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData',
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUserData'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->em = $container->get('doctrine')->getManager();
        $this->tokenStorage = $container->get('security.token_storage');

        $this->user = $this->em->getRepository('OroUserBundle:User');
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

        $this->tokenStorage->setToken(null);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
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

        foreach ($this->users as $index => $user) {
            //get default calendar, each user has default calendar after creation
            $calendar = $this->calendar->findDefaultCalendar($user->getId(), $this->organization->getId());
            if (!$calendar) {
                continue;
            }
            $this->setSecurityContext($calendar->getOwner());
            $events['recurring_events'] = $this->getRecurringEvents();
            foreach ($events as $typeEvents) {
                if (mt_rand(0, 1)) {
                    foreach ($typeEvents as $typeEvent) {
                        $event = clone $typeEvent;
                        $event->setIsOrganizer(true)->setOrganizerEmail($calendar->getOwner()->getEmail())
                            ->setOrganizerDisplayName(sprintf(
                                '%s %s',
                                $calendar->getOwner()->getFirstName(),
                                $calendar->getOwner()->getLastName()
                            ))->setOrganizerUser($calendar->getOwner());
                        $calendar->addEvent($event);
                    }
                }
            }
            $this->em->persist($calendar);
            if ($index > 0 && $index % 5 === 0) {
                $this->em->flush();
                $this->em->clear('Oro\Bundle\ActivityListBundle\Entity\ActivityOwner');
                $this->em->clear('Oro\Bundle\ActivityListBundle\Entity\ActivityList');
                $this->em->clear('Oro\Bundle\CalendarBundle\Entity\CalendarEvent');
                $this->em->clear('Oro\Bundle\CalendarBundle\Entity\Calendar');
            }
        }
        $this->em->flush();
        $this->em->clear('Oro\Bundle\ActivityListBundle\Entity\ActivityOwner');
        $this->em->clear('Oro\Bundle\ActivityListBundle\Entity\ActivityList');
        $this->em->clear('Oro\Bundle\CalendarBundle\Entity\CalendarEvent');
        $this->em->clear('Oro\Bundle\CalendarBundle\Entity\Calendar');
        $this->addRecurringEventExceptions();
    }

    protected function connectCalendars()
    {
        // first user is admin, often
        /** @var \Oro\Bundle\UserBundle\Entity\User $admin */
        $admin = $this->em->getRepository('OroUserBundle:User')
            ->createQueryBuilder('u')
            ->select('u')
            ->orderBy('u.id')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();
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
            $rawList = array_column($userIds, 'id', 'id');
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

    protected function setSecurityContext(User $user)
    {
        $token = new UsernamePasswordOrganizationToken(
            $user,
            $user->getUsername(),
            'main',
            $this->organization,
            $user->getUserRoles()
        );
        $this->tokenStorage->setToken($token);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * Returns the list of recurring events.
     *
     * @return array
     */
    protected function getRecurringEvents()
    {
        $baseEvent = new CalendarEvent();
        $baseRecurrence = new Recurrence();

        $recurringEvents = [];

        $day = new \DateTime('+2 day', $this->getTimeZone());
        $event = clone $baseEvent;
        $event->setTitle('Gym Visiting');
        $day->setTime(19, 0, 0);
        $event->setEnd(clone $day);
        $day->setTime(18, 0, 0);
        $event->setStart(clone $day);
        $event->setAllDay(true);
        $recurrence = clone $baseRecurrence;
        $recurrence->setRecurrenceType(Model\Recurrence::TYPE_DAILY);
        $recurrence->setInterval(3)
            ->setTimeZone('America/Los_Angeles')
            ->setStartTime($day)
            ->setOccurrences(12);
        $event->setRecurrence($recurrence);
        $recurringEvents[] = $event;

        $day = new \DateTime('+1 day', $this->getTimeZone());
        $event = clone $baseEvent;
        $event->setTitle('Standup meeting');
        $day->setTime(10, 15, 0);
        $event->setEnd(clone $day);
        $day->setTime(10, 0, 0);
        $event->setStart(clone $day);
        $event->setAllDay(false);
        $recurrence = clone $baseRecurrence;
        $recurrence->setRecurrenceType(Model\Recurrence::TYPE_WEEKLY);
        $recurrence->setInterval(1)
            ->setTimeZone('America/Los_Angeles')
            ->setDayOfWeek([
                Model\Recurrence::DAY_MONDAY,
                Model\Recurrence::DAY_TUESDAY,
                Model\Recurrence::DAY_WEDNESDAY,
                Model\Recurrence::DAY_THURSDAY,
                Model\Recurrence::DAY_FRIDAY
            ])
            ->setStartTime($day);
        $event->setRecurrence($recurrence);
        $recurringEvents[] = $event;

        $day = new \DateTime('-3 day', $this->getTimeZone());
        $event = clone $baseEvent;
        $event->setTitle('Monthly Team Meeting');
        $day->setTime(18, 0, 0);
        $event->setEnd(clone $day);
        $day->setTime(16, 0, 0);
        $event->setStart(clone $day);
        $event->setAllDay(false);
        $recurrence = clone $baseRecurrence;
        $recurrence->setRecurrenceType(Model\Recurrence::TYPE_MONTHLY);
        $recurrence->setInterval(2)
            ->setTimeZone('America/Los_Angeles')
            ->setDayOfMonth(1)
            ->setStartTime($day)
            ->setEndTime(new \DateTime('Dec 31', $this->getTimeZone()));
        $event->setRecurrence($recurrence);
        $recurringEvents[] = $event;

        $day = new \DateTime('+5 day', $this->getTimeZone());
        $event = clone $baseEvent;
        $event->setTitle('Update News');
        $day->setTime(14, 0, 0);
        $event->setEnd(clone $day);
        $day->setTime(10, 0, 0);
        $event->setStart(clone $day);
        $event->setAllDay(true);
        $recurrence = clone $baseRecurrence;
        $recurrence->setRecurrenceType(Model\Recurrence::TYPE_MONTH_N_TH);
        $recurrence->setInterval(2)
            ->setTimeZone('America/Los_Angeles')
            ->setInstance(Model\Recurrence::INSTANCE_THIRD)
            ->setDayOfWeek([Model\Recurrence::DAY_SATURDAY, Model\Recurrence::DAY_SUNDAY])
            ->setStartTime($day)
            ->setOccurrences(6);
        $event->setRecurrence($recurrence);
        $recurringEvents[] = $event;

        $day = new \DateTime('now', $this->getTimeZone());
        $event = clone $baseEvent;
        $event->setTitle('Yearly Conference');
        $day->setTime(19, 0, 0);
        $event->setEnd(clone $day);
        $day->setTime(10, 0, 0);
        $event->setStart(clone $day);
        $event->setAllDay(true);
        $recurrence = clone $baseRecurrence;
        $recurrence->setRecurrenceType(Model\Recurrence::TYPE_YEARLY);
        $recurrence->setInterval(12)
            ->setTimeZone('America/Los_Angeles')
            ->setDayOfMonth(1)
            ->setMonthOfYear(4)
            ->setStartTime($day);
        $event->setRecurrence($recurrence);
        $recurringEvents[] = $event;

        $day = new \DateTime('-2 day', $this->getTimeZone());
        $event = clone $baseEvent;
        $event->setTitle('New Year Party');
        $day->setTime(23, 0, 0);
        $event->setEnd(clone $day);
        $day->setTime(18, 0, 0);
        $event->setStart(clone $day);
        $event->setAllDay(true);
        $recurrence = clone $baseRecurrence;
        $recurrence->setRecurrenceType(Model\Recurrence::TYPE_YEAR_N_TH);
        $recurrence->setInterval(12)
            ->setTimeZone('America/Los_Angeles')
            ->setInstance(Model\Recurrence::INSTANCE_LAST)
            ->setDayOfWeek([Model\Recurrence::DAY_SATURDAY])
            ->setMonthOfYear(12)
            ->setStartTime($day);
        $event->setRecurrence($recurrence);
        $recurringEvents[] = $event;

        return $recurringEvents;
    }

    /**
     * Adds exceptions to recurring events.
     */
    protected function addRecurringEventExceptions()
    {
        $event = $this->em->getRepository('OroCalendarBundle:CalendarEvent')->findOneBy(['title' => 'Standup meeting']);
        $day = new \DateTime('next friday', $this->getTimeZone());
        $day->setTime(10, 0, 0);
        $exception = new CalendarEvent();
        $exception->setTitle('Changed Standup meeting');
        $exception->setOriginalStart(clone $day);
        $day->setTime(9, 15, 0);
        $exception->setEnd(clone $day);
        $day->setTime(9, 0, 0);
        $exception->setStart(clone $day)
            ->setCalendar($event->getCalendar())
            ->setAllDay(true);
        $event->addRecurringEventException($exception);

        $day = new \DateTime('next monday', $this->getTimeZone());
        $day->setTime(10, 0, 0);
        $exception = new CalendarEvent();
        $exception->setTitle('Evening Standup meeting');
        $exception->setOriginalStart(clone $day);
        $day->setTime(19, 15, 0);
        $exception->setEnd(clone $day);
        $day->setTime(19, 0, 0);
        $exception->setStart(clone $day)
            ->setCalendar($event->getCalendar())
            ->setAllDay(false);
        $event->addRecurringEventException($exception);

        $day = new \DateTime('first wednesday of next month', $this->getTimeZone());
        $day->setTime(10, 0, 0);
        $exception = new CalendarEvent();
        $exception->setTitle('Late meeting');
        $exception->setOriginalStart(clone $day);
        $day->setTime(23, 15, 0);
        $exception->setEnd(clone $day);
        $day->setTime(23, 0, 0);
        $exception->setStart(clone $day)
            ->setCalendar($event->getCalendar())
            ->setAllDay(false);
        $event->addRecurringEventException($exception);

        $this->em->persist($event);

        $this->em->flush();
    }

    /**
     * @return \DateTimeZone
     */
    protected function getTimeZone()
    {
        if ($this->timeZone === null) {
            $this->timeZone = new \DateTimeZone('UTC');
        }

        return $this->timeZone;
    }
}
