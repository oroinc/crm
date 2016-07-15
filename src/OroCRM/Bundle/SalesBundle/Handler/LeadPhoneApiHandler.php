<?php

namespace OroCRM\Bundle\SalesBundle\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\EntityBundle\Form\EntityField\Handler\Processor\AbstractEntityApiHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Class LeadPhoneApiHandler
 * @package OroCRM\Bundle\SalesBundle\Handler
 */
class LeadPhoneApiHandler extends AbstractEntityApiHandler
{
    const ENTITY_CLASS = 'OroCRM\Bundle\SalesBundle\Entity\LeadPhone';

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @param Registry $doctrine
     * @param SecurityFacade $securityFacade
     */
    public function __construct(Registry $doctrine, SecurityFacade $securityFacade)
    {
        $this->doctrine = $doctrine;
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeProcess($entity)
    {
        //check owner (Lead) entity with 'edit' permission
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
        $em = $this->doctrine->getEntityManager();
        $em->persist($owner);
        $em->flush();

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
