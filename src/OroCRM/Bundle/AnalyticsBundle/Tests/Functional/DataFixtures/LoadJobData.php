<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use JMS\JobQueueBundle\Entity\Job;

class LoadJobData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = [
        'canceled' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job1',
            'args' => [],
            'confirmed' => true,
            'state' => [Job::STATE_CANCELED]
        ],
        'canceled with args' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job2',
            'args' => ['--channel=1'],
            'confirmed' => true,
            'state' => [Job::STATE_CANCELED]
        ],
        'finished' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job3',
            'args' => [],
            'confirmed' => true,
            'state' => [Job::STATE_RUNNING, Job::STATE_FINISHED]
        ],
        'finished with args' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job4',
            'args' => ['--channel=1'],
            'confirmed' => true,
            'state' => [Job::STATE_RUNNING, Job::STATE_FINISHED]
        ],
        'failed' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job3',
            'args' => [],
            'confirmed' => true,
            'state' => [Job::STATE_RUNNING, Job::STATE_FAILED]
        ],
        'failed with args' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job4',
            'args' => ['--channel=1'],
            'confirmed' => true,
            'state' => [Job::STATE_RUNNING, Job::STATE_FAILED]
        ],
        'terminated' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job5',
            'args' => [],
            'confirmed' => true,
            'state' => [Job::STATE_RUNNING, Job::STATE_TERMINATED]
        ],
        'terminated with args' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job6',
            'args' => ['--channel=1'],
            'confirmed' => true,
            'state' => [Job::STATE_RUNNING, Job::STATE_TERMINATED]
        ],
        'incomplete' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job7',
            'args' => [],
            'confirmed' => true,
            'state' => [Job::STATE_RUNNING, Job::STATE_INCOMPLETE]
        ],
        'incomplete with args' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job8',
            'args' => ['--channel=1'],
            'confirmed' => true,
            'state' => [Job::STATE_RUNNING, Job::STATE_INCOMPLETE]
        ],
        'new' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job9',
            'confirmed' => false,
            'args' => [],
            'state' => []
        ],
        'new with args' => [
            'command' => 'oro:cron:analytic:calculate',
            'reference' => 'job10',
            'args' => ['--channel=1'],
            'confirmed' => false,
            'state' => []
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            $entity = new Job($data['command'], $data['args'], $data['confirmed']);

            foreach ($data['state'] as $state) {
                $entity->setState($state);
            }

            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
