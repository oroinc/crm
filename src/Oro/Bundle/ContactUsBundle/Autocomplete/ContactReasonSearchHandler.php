<?php

namespace Oro\Bundle\ContactUsBundle\Autocomplete;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * ORM search for contact reasons by default title
 */
class ContactReasonSearchHandler implements SearchHandlerInterface
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /** @var array */
    private $displayFields = ['defaultTitle'];

    public function __construct(DoctrineHelper $doctrineHelper, PropertyAccessorInterface $propertyAccessor)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function search($query, $page, $perPage, $searchById = false)
    {
        $repository = $this->doctrineHelper->getEntityRepository(ContactReason::class);
        $queryBuilder = $repository->createQueryBuilder('contact_reason');
        $condition = $queryBuilder->expr()->isNull('titles.localization');
        $queryBuilder->innerJoin('contact_reason.titles', 'titles', Join::WITH, $condition);
        $queryBuilder->where($queryBuilder->expr()->isNull('contact_reason.deletedAt'));

        if ($query) {
            $queryBuilder->andWhere($queryBuilder->expr()->like('titles.string', ':title'));
            $queryBuilder->setParameter('title', '%' . $query . '%');
        }

        /** @var ContactReason[] $result */
        $result = $queryBuilder->getQuery()->getResult();

        $data = [];
        foreach ($result as $contactReason) {
            $data[] = $this->convertItem($contactReason);
        }

        return [
            'results' => $data,
            'more' => false
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return $this->displayFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        return ContactReason::class;
    }

    /**
     * {@inheritdoc}
     */
    public function convertItem($item)
    {
        $result = [];

        $idFieldName = 'id';
        $result[$idFieldName] = $this->propertyAccessor->getValue($item, $idFieldName);

        foreach ($this->getProperties() as $field) {
            $result[$field] = (string) $this->propertyAccessor->getValue($item, $field);
        }

        return $result;
    }
}
