<?php

namespace OroCRM\Bundle\MagentoBundle\Autocomplete;

use OroCRM\Bundle\MagentoBundle\Entity\Store;

class StoreSearchHandler extends IntegrationAwareSearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        list($searchTerm, $channelId) = explode(';', $search);

        $queryBuilder = $this->entityRepository->createQueryBuilder('e');
        $queryBuilder
            ->leftJoin('e.website', 'w')
            ->where($queryBuilder->expr()->like('LOWER(e.name)', ':searchTerm'))
            ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%')
            ->addOrderBy('w.name', 'ASC')
            ->addOrderBy('e.name', 'ASC');

        $dataChannel = $this->getDataChannelById($channelId);
        if ($dataChannel) {
            $queryBuilder->andWhere('e.channel = :channel')
                ->setParameter('channel', $dataChannel->getDataSource());
        } else {
            $queryBuilder->andWhere('1 = 0');
        }

        $query = $this->aclHelper->apply($queryBuilder, 'VIEW');

        return $query->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function search($query, $page, $perPage, $searchById = false)
    {
        $this->checkAllDependenciesInjected();

        if ($searchById) {
            $items = $this->findById($query);

            return [
                'results' => [$this->convertItem(reset($items))],
                'more'    => false
            ];
        } else {
            $items = $this->searchEntities($query, 0, null);

            return [
                'results' => $this->convertItems($items),
                'more'    => false
            ];
        }
    }

    /**
     * @param Store[] $items
     * @return array
     */
    protected function convertItems(array $items)
    {
        $grouped = [];
        foreach ($items as $item) {
            $groupingKey = $item->getWebsite()->getName();
            $grouped[$groupingKey][] = $item;
        }

        $result = [];
        foreach ($grouped as $group => $elements) {
            $gropedItem = [
                'name' => $group
            ];
            foreach ($elements as $element) {
                $gropedItem['children'][] = $this->convertItem($element);
            }
            $result[] = $gropedItem;
        }

        return $result;
    }
}
