<?php

namespace OroCRM\Bundle\CampaignBundle\Entity\Repository;

use Doctrine\DBAL\Types\Type;
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
            ->andWhere($qb->expr()->lte('email_campaign.scheduledFor', ':currentTimestamp'))
            ->setParameter('sent', false, Type::BOOLEAN)
            ->setParameter('scheduleType', EmailCampaign::SCHEDULE_DEFERRED, Type::STRING)
            ->setParameter('currentTimestamp', new \DateTime('now', new \DateTimeZone('UTC')), Type::DATETIME);

        return $qb->getQuery()->getResult();
    }
}
