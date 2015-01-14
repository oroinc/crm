<?php

namespace OroCRM\Bundle\CampaignBundle\Command;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\CampaignBundle\Entity\Campaign;
use OroCRM\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;
use OroCRM\Bundle\CampaignBundle\Entity\Repository\TrackingEventSummaryRepository;
use OroCRM\Bundle\CampaignBundle\Entity\TrackingEventSummary;

/**
 * Calculate Tracking Event Summary
 */
class CalculateTrackingEventSummaryCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const NAME = 'oro:cron:calculate-tracking-event-summary';

    /**
     * @var TrackingEventSummaryRepository
     */
    protected $trackingEventRepository;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $trackingWebsiteEntityClass;

    /**
     * Run command at 00:01 every day.
     *
     * @return string
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
        $this->setName(self::NAME)
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
        $em = $this->getEntityManager($this->getCampaignEntityClass());
        foreach ($campaigns as $campaign) {
            $output->writeln(sprintf('<info>Calculating statistic for campaign</info>: %s', $campaign->getName()));

            $this->calculateForCampaign($campaign);

            $refreshDate = new \DateTime('-1 day', new \DateTimeZone('UTC'));
            $campaign->setReportRefreshDate($refreshDate);
            $em->persist($campaign);
        }

        $em->flush();
    }

    /**
     * @param Campaign $campaign
     */
    protected function calculateForCampaign(Campaign $campaign)
    {
        $trackingEventRepository = $this->getTrackingEventSummaryRepository();
        $events = $trackingEventRepository->getSummarizedStatistic($campaign);

        $em = $this->getEntityManager($this->getTrackingEventSummaryEntityClass());
        foreach ($events as $event) {
            $website = $this->getDoctrineHelper()
                ->getEntityReference(
                    $this->getTrackingWebsiteEntityClass(),
                    $event['websiteId']
                );

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
        return $this->getDoctrineHelper()->getEntityRepository($this->getCampaignEntityClass());
    }

    /**
     * @return TrackingEventSummaryRepository
     */
    protected function getTrackingEventSummaryRepository()
    {
        if (!$this->trackingEventRepository) {
            $this->trackingEventRepository = $this
                ->getDoctrineHelper()
                ->getEntityRepository($this->getTrackingEventSummaryEntityClass());
        }

        return $this->trackingEventRepository;
    }

    /**
     * @param string $class
     * @return EntityManager
     */
    protected function getEntityManager($class)
    {
        return $this->getDoctrineHelper()->getEntityManager($class);
    }

    /**
     * @return DoctrineHelper
     */
    protected function getDoctrineHelper()
    {
        if (!$this->doctrineHelper) {
            $this->doctrineHelper = $this->getContainer()->get('oro_entity.doctrine_helper');
        }

        return $this->doctrineHelper;
    }

    /**
     * @return string
     */
    protected function getCampaignEntityClass()
    {
        return $this->getContainer()->getParameter('orocrm_campaign.entity.class');
    }

    /**
     * @return string
     */
    protected function getTrackingEventSummaryEntityClass()
    {
        return $this->getContainer()->getParameter('orocrm_campaign.tracking_event_summary.class');
    }

    /**
     * @return string
     */
    protected function getTrackingWebsiteEntityClass()
    {
        if (!$this->trackingWebsiteEntityClass) {
            $this->trackingWebsiteEntityClass = $this->getContainer()
                ->getParameter('oro_tracking.tracking_website.class');
        }

        return $this->trackingWebsiteEntityClass;
    }
}
