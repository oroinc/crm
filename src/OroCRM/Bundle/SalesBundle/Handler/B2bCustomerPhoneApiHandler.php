<?php

namespace OroCRM\Bundle\SalesBundle\Handler;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\EntityBundle\Form\EntityField\Handler\Processor\AbstractEntityApiHandler;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Class B2bCustomerPhoneApiHandler
 * @package OroCRM\Bundle\SalesBundle\Handler
 */
class B2bCustomerPhoneApiHandler extends AbstractEntityApiHandler
{
    const ENTITY_CLASS = 'OroCRM\Bundle\SalesBundle\Entity\B2bCustomerPhone';

    /**
     * @var OroEntityManager
     */
    protected $entityManager;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @param OroEntityManager $entityManager
     * @param SecurityFacade $securityFacade
     */
    public function __construct(OroEntityManager $entityManager, SecurityFacade $securityFacade)
    {
        $this->entityManager = $entityManager;
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeProcess($entity)
    {
        //check owner (B2bCustomer) entity with 'edit' permission
        if (!$this->securityFacade->isGranted('EDIT', $entity->getOwner())) {
            throw new AccessDeniedException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterProcess($entity)
    {
        $owner = $entity->getOwner();
        $owner->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $changeSet = $this->getChangeSet($owner);
        $this->entityManager->persist($owner);
        $this->entityManager->flush();

        return $changeSet;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return self::ENTITY_CLASS;
    }

    /**
     * @param $entity
     *
     * @return array
     */
    protected function getChangeSet($entity)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $response = [
            'fields' => []
        ];

        if ($accessor->isReadable($entity, 'updatedAt')) {
            $response['fields']['updatedAt'] = $accessor->getValue($entity, 'updatedAt');
        }

        return $response;
    }
}
