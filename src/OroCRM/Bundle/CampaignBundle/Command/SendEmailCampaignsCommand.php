<?php

namespace OroCRM\Bundle\CampaignBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Entity\Repository\EmailCampaignRepository;
use OroCRM\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;

/**
 * Command to send scheduled email campaigns
 */
class SendEmailCampaignsCommand extends ContainerAwareCommand implements CronCommandInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '*/1 * * * *';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:cron:send-email-campaigns')
            ->setDescription('Send email campaigns');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $emailCampaigns = $this->getEmailCampaignRepository()->findEmailCampaignsToSend();

        if (!$emailCampaigns) {
            $output->writeln('<info>No email campaigns to send</info>');
            return;
        }

        $output->writeln(
            sprintf('<comment>Email campaigns to send:</comment> %d', count($emailCampaigns))
        );

        $this->send($output, $emailCampaigns);
        $output->writeln(sprintf('<info>Finished email campaigns sending</info>'));
    }

    /**
     * Send email campaigns
     *
     * @param OutputInterface $output
     * @param EmailCampaign[] $emailCampaigns
     */
    protected function send($output, array $emailCampaigns)
    {
        $senderFactory = $this->getSenderFactory();

        foreach ($emailCampaigns as $emailCampaign) {
            $output->writeln(sprintf('<info>Sending email campaign</info>: %s', $emailCampaign->getName()));

            $sender = $senderFactory->getSender($emailCampaign);
            $sender->send($emailCampaign);
        }
    }

    /**
     * @return EmailCampaignSenderBuilder
     */
    protected function getSenderFactory()
    {
        return $this->getContainer()->get('orocrm_campaign.email_campaign.sender.builder');
    }

    /**
     * @return EmailCampaignRepository
     */
    protected function getEmailCampaignRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository('OroCRMCampaignBundle:EmailCampaign');
    }
}
