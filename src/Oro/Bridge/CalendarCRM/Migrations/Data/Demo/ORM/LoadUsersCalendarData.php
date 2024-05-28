<?php

namespace Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Entity\ActivityOwner;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Entity\CalendarProperty;
use Oro\Bundle\CalendarBundle\Entity\Recurrence;
use Oro\Bundle\CalendarBundle\Model;
use Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUserData;
use Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Loads user calendars
 */
class LoadUsersCalendarData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    private ?\DateTimeZone $timeZone = null;

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadUsersData::class, LoadUserData::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $users = $manager->getRepository(User::class)->findAll();
        $this->loadCalendars($manager, $tokenStorage, $users);
        $this->connectCalendars($manager, $users);
        $tokenStorage->setToken(null);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function loadCalendars(ObjectManager $manager, TokenStorageInterface $tokenStorage, array $users): void
    {
        $days = $this->getDatePeriod();
        $events = [];
        foreach ($days as $day) {
            /** @var \DateTime $day */
            if (!$this->isWeekend($day)) {
                //work day
                $event = new CalendarEvent();
                $event->setTitle('Work Reminder');
                $day->setTime(8, 0);
                $event->setStart(clone $day);
                $day->setTime(18, 0);
                $event->setEnd(clone $day);
                $event->setAllDay(true);
                $events['workday'][] = $event;
                //call
                $event = new CalendarEvent();
                $event->setTitle('Client Call');
                $day->setTime(11, 0);
                $event->setStart(clone $day);
                $day->setTime(12, 0);
                $event->setEnd(clone $day);
                $event->setAllDay(false);
                $events['call'][] = $event;
                //meeting
                $event = new CalendarEvent();
                $event->setTitle('Meeting');
                $day->setTime(16, 0);
                $event->setStart(clone $day);
                $day->setTime(18, 0);
                $event->setEnd(clone $day);
                $event->setAllDay(false);
                $events['meeting'][] = $event;
                //lunch
                $event = new CalendarEvent();
                $event->setTitle('Lunch');
                $day->setTime(12, 0);
                $event->setStart(clone $day);
                $day->setTime(12, 30);
                $event->setEnd(clone $day);
                $event->setAllDay(false);
                $events['lunch'][] = $event;
                //business trip
                $event = new CalendarEvent();
                $event->setTitle('Business trip');
                $day->setTime(0, 0);
                $event->setStart(clone $day);
                $day->setTime(0, 0);
                $day->add(\DateInterval::createFromDateString('+3 days'));
                $event->setEnd(clone $day);
                $event->setAllDay(true);
                $events['b_trip'][] = $event;
            } else {
                $event = new CalendarEvent();
                $event->setTitle('Weekend');
                $day->setTime(8, 0);
                $event->setStart(clone $day);
                $day->setTime(18, 0);
                $event->setEnd(clone $day);
                $event->setAllDay(true);
                $events['weekend'][] = $event;
            }
        }

        $calendarRepository = $manager->getRepository(Calendar::class);
        $organization = $this->getReference('default_organization');
        foreach ($users as $index => $user) {
            //get default calendar, each user has default calendar after creation
            $calendar = $calendarRepository->findDefaultCalendar($user->getId(), $organization->getId());
            if (!$calendar) {
                continue;
            }
            $calendarOwner = $calendar->getOwner();
            $tokenStorage->setToken(new UsernamePasswordOrganizationToken(
                $calendarOwner,
                'main',
                $organization,
                $calendarOwner->getUserRoles()
            ));
            $events['recurring_events'] = $this->getRecurringEvents();
            foreach ($events as $typeEvents) {
                if (mt_rand(0, 1)) {
                    foreach ($typeEvents as $typeEvent) {
                        $event = clone $typeEvent;
                        $event->setIsOrganizer(true)->setOrganizerEmail($calendarOwner->getEmail())
                            ->setOrganizerDisplayName(sprintf(
                                '%s %s',
                                $calendarOwner->getFirstName(),
                                $calendarOwner->getLastName()
                            ))->setOrganizerUser($calendarOwner);
                        $calendar->addEvent($event);
                    }
                }
            }
            $manager->persist($calendar);
            if ($index > 0 && $index % 5 === 0) {
                $manager->flush();
                $manager->clear(ActivityOwner::class);
                $manager->clear(ActivityList::class);
                $manager->clear(CalendarEvent::class);
                $manager->clear(Calendar::class);
            }
        }
        $manager->flush();
        $manager->clear(ActivityOwner::class);
        $manager->clear(ActivityList::class);
        $manager->clear(CalendarEvent::class);
        $manager->clear(Calendar::class);
        $this->addRecurringEventExceptions($manager);
    }

    private function connectCalendars(ObjectManager $manager, array $users): void
    {
        $userRepository = $manager->getRepository(User::class);
        $calendarRepository = $manager->getRepository(Calendar::class);

        // first user is admin, often
        /** @var User $admin */
        $admin = $userRepository
            ->createQueryBuilder('u')
            ->select('u')
            ->orderBy('u.id')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();
        /** @var Calendar $calendarAdmin */
        $calendarAdmin = $calendarRepository->findDefaultCalendar(
            $admin->getId(),
            $admin->getOrganization()->getId()
        );

        /** @var User $sale */
        $sale = $userRepository->findOneBy(['username' => 'sale']);
        /** @var Calendar $calendarSale */
        $calendarSale = $calendarRepository->findDefaultCalendar(
            $sale->getId(),
            $sale->getOrganization()->getId()
        );

        /** @var User $market */
        $market = $userRepository->findOneBy(['username' => 'marketing']);
        /** @var Calendar $calendarMarket */
        $calendarMarket = $calendarRepository->findDefaultCalendar(
            $market->getId(),
            $market->getOrganization()->getId()
        );

        $users = $this->getRandomUsers($manager, $users);
        foreach ($users as $user) {
            if (in_array($user->getId(), [$admin->getId(), $sale->getId(), $market->getId()])) {
                //to prevent self assignment
                continue;
            }
            /** @var Calendar $calendar */
            $calendar = $calendarRepository->findDefaultCalendar(
                $user->getId(),
                $user->getOrganization()->getId()
            );

            if (mt_rand(0, 1)) {
                $calendarProperty = new CalendarProperty();
                $calendarProperty
                    ->setTargetCalendar($calendarAdmin)
                    ->setCalendarAlias('user')
                    ->setCalendar($calendar->getId());

                $manager->persist($calendarProperty);
            }

            if (mt_rand(0, 1)) {
                $calendarProperty = new CalendarProperty();
                $calendarProperty
                    ->setTargetCalendar($calendarSale)
                    ->setCalendarAlias('user')
                    ->setCalendar($calendar->getId());

                $manager->persist($calendarProperty);
            }

            if (mt_rand(0, 1)) {
                $calendarProperty = new CalendarProperty();
                $calendarProperty
                    ->setTargetCalendar($calendarMarket)
                    ->setCalendarAlias('user')
                    ->setCalendar($calendar->getId());

                $manager->persist($calendarProperty);
            }

            $manager->persist($calendar);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param User[]        $users
     *
     * @return User[]
     */
    private function getRandomUsers(ObjectManager $manager, array $users): array
    {
        $userRepository = $manager->getRepository(User::class);
        $userIds = $userRepository->createQueryBuilder('u')
            ->select('u.id')
            ->getQuery()
            ->getScalarResult();

        $limit = 5;
        if (\count($userIds) > $limit) {
            $rawList = array_column($userIds, 'id', 'id');
            $keyList = array_rand($rawList, $limit);

            $criteria = new Criteria();
            $criteria->where(Criteria::expr()->in('id', $keyList));

            $result = $userRepository->createQueryBuilder('u')
                ->addCriteria($criteria)
                ->getQuery()
                ->getResult();
        } else {
            $result = $users;
        }

        return $result;
    }

    private function getDatePeriod(): \DatePeriod
    {
        return new \DatePeriod(
            new \DateTime('now'),
            \DateInterval::createFromDateString('+1 day'),
            new \DateTime('now +14 day'),
            \DatePeriod::EXCLUDE_START_DATE
        );
    }

    private function isWeekend(\DateTime $dateTime): bool
    {
        return \in_array((int)$dateTime->format('w'), [0, 6], true);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function getRecurringEvents(): array
    {
        $baseEvent = new CalendarEvent();
        $baseRecurrence = new Recurrence();

        $recurringEvents = [];

        $day = new \DateTime('+2 day', $this->getTimeZone());
        $event = clone $baseEvent;
        $event->setTitle('Gym Visiting');
        $day->setTime(19, 0);
        $event->setEnd(clone $day);
        $day->setTime(18, 0);
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
        $day->setTime(10, 15);
        $event->setEnd(clone $day);
        $day->setTime(10, 0);
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
        $day->setTime(18, 0);
        $event->setEnd(clone $day);
        $day->setTime(16, 0);
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
        $day->setTime(14, 0);
        $event->setEnd(clone $day);
        $day->setTime(10, 0);
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
        $day->setTime(19, 0);
        $event->setEnd(clone $day);
        $day->setTime(10, 0);
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
        $day->setTime(23, 0);
        $event->setEnd(clone $day);
        $day->setTime(18, 0);
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
    private function addRecurringEventExceptions(ObjectManager $manager): void
    {
        $event = $manager->getRepository(CalendarEvent::class)->findOneBy(['title' => 'Standup meeting']);
        $day = new \DateTime('next friday', $this->getTimeZone());
        $day->setTime(10, 0);
        $exception = new CalendarEvent();
        $exception->setTitle('Changed Standup meeting');
        $exception->setOriginalStart(clone $day);
        $day->setTime(9, 15);
        $exception->setEnd(clone $day);
        $day->setTime(9, 0);
        $exception->setStart(clone $day)
            ->setCalendar($event->getCalendar())
            ->setAllDay(true);
        $event->addRecurringEventException($exception);

        $day = new \DateTime('next monday', $this->getTimeZone());
        $day->setTime(10, 0);
        $exception = new CalendarEvent();
        $exception->setTitle('Evening Standup meeting');
        $exception->setOriginalStart(clone $day);
        $day->setTime(19, 15);
        $exception->setEnd(clone $day);
        $day->setTime(19, 0);
        $exception->setStart(clone $day)
            ->setCalendar($event->getCalendar())
            ->setAllDay(false);
        $event->addRecurringEventException($exception);

        $day = new \DateTime('first wednesday of next month', $this->getTimeZone());
        $day->setTime(10, 0);
        $exception = new CalendarEvent();
        $exception->setTitle('Late meeting');
        $exception->setOriginalStart(clone $day);
        $day->setTime(23, 15);
        $exception->setEnd(clone $day);
        $day->setTime(23, 0);
        $exception->setStart(clone $day)
            ->setCalendar($event->getCalendar())
            ->setAllDay(false);
        $event->addRecurringEventException($exception);

        $manager->persist($event);

        $manager->flush();
    }

    private function getTimeZone(): \DateTimeZone
    {
        if (null === $this->timeZone) {
            $this->timeZone = new \DateTimeZone('UTC');
        }

        return $this->timeZone;
    }
}
