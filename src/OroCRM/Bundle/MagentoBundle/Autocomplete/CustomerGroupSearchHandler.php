<?php

namespace OroCRM\Bundle\MagentoBundle\Autocomplete;

class CustomerGroupSearchHandler extends IntegrationAwareSearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        list($searchTerm, $channelId) = explode(';', $search);

        $queryBuilder = $this->entityRepository->createQueryBuilder('e');
        $queryBuilder
            ->where($queryBuilder->expr()->like('LOWER(e.name)', ':searchTerm'))
            ->andWhere('e.originId > 0')
            ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%')

            ->addOrderBy('e.name', 'ASC')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults);

        $dataChannel = $this->getDataChannelById($channelId);
        if ($dataChannel && $this->securityFacade->isGranted('oro_integration_assign')) {
            $queryBuilder->andWhere('e.channel = :channel')
                ->setParameter('channel', $dataChannel->getDataSource());
        } else {
            $queryBuilder->andWhere('1 = 0');
        }

        $query = $this->aclHelper->apply($queryBuilder, 'VIEW');

        return $query->getResult();
    }
}
