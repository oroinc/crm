<?php

namespace OroCRM\Bundle\CampaignBundle\Entity\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

class EmailCampaignStatisticsRepository extends EntityRepository
{
    /**
     * @param EmailCampaign $emailCampaign
     * @return array
     */
    public function getEmailCampaignStats(EmailCampaign $emailCampaign)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select(
                [
                    'SUM(ecs.openCount) as open',
                    'SUM(ecs.clickCount) as click',
                    'SUM(ecs.bounceCount) as bounce',
                    'SUM(ecs.abuseCount) as abuse',
                    'SUM(ecs.unsubscribeCount) as unsubscribe'
                ]
            )
            ->from('OroCRMCampaignBundle:EmailCampaignStatistics', 'ecs')
            ->where($qb->expr()->eq('ecs.emailCampaign', ':emailCampaign'))
            ->setParameter('emailCampaign', $emailCampaign);

        return $qb->getQuery()->getSingleResult();
    }
}
