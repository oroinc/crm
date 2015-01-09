<?php

namespace OroCRM\Bundle\CampaignBundle\Command;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\CampaignBundle\Entity\TrackingEventSummary;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use OroCRM\Bundle\CampaignBundle\Entity\Campaign;
use OroCRM\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;
use OroCRM\Bundle\CampaignBundle\Entity\Repository\TrackingEventSummaryRepository;

/**
 * Calculate Tracking Event Summary
 */
class CalculateTrackingEventSummaryCommand extends ContainerAwareCommand implements CronCommandInterface
{
    /**
     * @var TrackingEventSummaryRepository
     */
    protected $trackingEventRepository;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '1 0 * * *';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:cron:calculate-tracking-event-summary')
            ->setDescription('Calculate Tracking Event Summary');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $campaigns = $this->getCampaignRepository()->findAll();

        if (!$campaigns) {
            $output->writeln('<info>No campaigns found</info>');
            return;
        }

        $output->writeln(
            sprintf('<comment>Campaigns to calculate:</comment> %d', count($campaigns))
        );

        $this->calculate($output, $campaigns);
        $output->writeln(sprintf('<info>Finished campaigns statistic calculation</info>'));
    }

    /**
     * Calculate tracking event statistic for campaigns
     *
     * @param OutputInterface $output
     * @param Campaign[] $campaigns
     */
    protected function calculate($output, array $campaigns)
    {
        $em = $this->getEntityManager('OroCRMCampaignBundle:Campaign');
        foreach ($campaigns as $campaign) {
            $output->writeln(sprintf('<info>Calculating statistic for campaign</info>: %s', $campaign->getName()));

            $this->calculateForCampaign($campaign);

            $refreshDate = new \DateTime('-1 day', new \DateTimeZone('UTC'));
            $campaign->setReportRefreshDate($refreshDate);
            $em->persist($campaign);
        }

        $em->flush();
    }

    protected function calculateForCampaign(Campaign $campaign)
    {
        $trackingEventRepository = $this->getTrackingEventSummaryRepository();
        $events = $trackingEventRepository->getSummarizedStatistic($campaign);

        $em = $this->getEntityManager('OroCRMCampaignBundle:TrackingEventSummary');
        foreach ($events as $event) {
            $website = $this->getDoctrineHelper()
                ->getEntityReference('OroTrackingBundle:TrackingWebsite', $event['websiteId']);

            $summary = new TrackingEventSummary();
            $summary->setCode($campaign->getCode());
            $summary->setWebsite($website);
            $summary->setName($event['name']);
            $summary->setVisitCount($event['visitCount']);
            $summary->setLoggedAt(new \DateTime($event['loggedAtDate'], new \DateTimeZone('UTC')));

            $em->persist($summary);
        }

        $em->flush();
    }

    /**
     * @return CampaignRepository
     */
    protected function getCampaignRepository()
    {
        return $this->getDoctrineHelper()->getEntityRepository('OroCRMCampaignBundle:Campaign');
    }

    /**
     * @return TrackingEventSummaryRepository
     */
    protected function getTrackingEventSummaryRepository()
    {
        if (!$this->trackingEventRepository) {
            $this->trackingEventRepository = $this
                ->getDoctrineHelper()
                ->getEntityRepository('OroCRMCampaignBundle:TrackingEventSummary');
        }

        return $this->trackingEventRepository;
    }

    protected function getEntityManager($class)
    {
        return $this->getDoctrineHelper()->getEntityManager($class);
    }

    protected function getDoctrineHelper()
    {
        if (!$this->doctrineHelper) {
            $this->doctrineHelper = $this->getContainer()->get('oro_entity.doctrine_helper');
        }

        return $this->doctrineHelper;
    }
}
