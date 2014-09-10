<?php

namespace OroCRM\Bundle\CampaignBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

class EmailCampaignRepository extends EntityRepository
{
    /**
     * @return EmailCampaign[]
     */
    public function findEmailCampaignsToSend()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('email_campaign')
            ->from('OroCRMCampaignBundle:EmailCampaign', 'email_campaign')
            ->where($qb->expr()->eq('email_campaign.sent', ':sent'))
            ->andWhere($qb->expr()->eq('email_campaign.schedule', ':scheduleType'))
            ->andWhere($qb->expr()->isNotNull('email_campaign.scheduledFor'))
            ->andWhere($qb->expr()->lte('email_campaign.scheduledFor', 'CURRENT_TIMESTAMP()'))
            ->setParameter('sent', false)
            ->setParameter('scheduleType', EmailCampaign::SCHEDULE_DEFERRED);

        return $qb->getQuery()->getResult();
    }
}
