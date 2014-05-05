<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactSubscriber implements EventSubscriber
{
    /**
     * @var ServiceLink
     */
    protected $schedulerServiceLink;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    protected $checkEntityClasses = [
        'OroCRM\Bundle\ContactBundle\Entity\Contact'        => [
            'fields' => [
                'firstName',
                'middleName',
                'lastName',
                'gender',
                'birthday',
                'email',
                'emails',
                'addresses'
            ]
        ],
        'OroCRM\Bundle\ContactBundle\Entity\ContactAddress' => [
            'findContactMethod' => 'getOwner'
        ],
        'OroCRM\Bundle\ContactBundle\Entity\ContactEmail'   => [
            'findContactMethod' => 'getOwner'
        ],
        'OroCRM\Bundle\ContactBundle\Entity\ContactPhone'   => [
            'findContactMethod' => 'getOwner'
        ]
    ];

    /**
     * Array with processed records
     *
     * @var array
     */
    protected $processedIds = [];

    public function __construct(SecurityFacade $securityFacade, ServiceLink $schedulerServiceLink)
    {
        $this->schedulerServiceLink = $schedulerServiceLink;
        $this->securityFacade       = $securityFacade;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            // @codingStandardsIgnoreStart
            Events::postFlush
            // @codingStandardsIgnoreEnd
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        $this->processUpdates($em);
        $this->processDeletes($em);
    }

    /**
     * Process Delete entities
     *
     * @param EntityManager $em
     */
    protected function processDeletes(EntityManager $em)
    {
        $uow      = $em->getUnitOfWork();
        $entities = $uow->getScheduledEntityDeletions();
        foreach ($entities as $entity) {
            foreach ($this->checkEntityClasses as $classNames => $classMapConfig) {
                if ($entity instanceof $classNames) {
                    if (isset ($classMapConfig['findContactMethod'])) {
                        $method        = $classMapConfig['findContactMethod'];
                        $contactEntity = $entity->$method();
                    } else {
                        $contactEntity = $entity;
                    }

                    $this->scheduleSync($contactEntity, $em);
                }
            }
        }
    }

    /**
     * Process updated and inserted entities
     *
     * @param EntityManager $em
     */
    protected function processUpdates(EntityManager $em)
    {
        $uow      = $em->getUnitOfWork();
        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
            foreach ($this->checkEntityClasses as $classNames => $classMapConfig) {
                if ($entity instanceof $classNames) {
                    if (isset ($classMapConfig['findContactMethod'])) {
                        $method        = $classMapConfig['findContactMethod'];
                        $contactEntity = $entity->$method();
                        $changed       = true;
                    } else {
                        $changed       = false;
                        $contactEntity = $entity;

                        $chaneSet = $uow->getEntityChangeSet($contactEntity);
                        foreach (array_keys($chaneSet) as $fieldName) {
                            if (in_array($fieldName, $classMapConfig['fields'])) {
                                $changed = true;
                                break;
                            }
                        }
                    }

                    if ($changed) {
                        $this->scheduleSync($contactEntity, $em);
                    }
                }
            }
        }
    }

    /**
     * @param Contact       $contactEntity
     * @param EntityManager $em
     */
    protected function scheduleSync(Contact $contactEntity, EntityManager $em)
    {
        if (!in_array($contactEntity->getId(), $this->processedIds)) {
            $magentoCustomer = $em->getRepository('OroCRMMagentoBundle:Customer')
                ->getCustomerRelatedToContact($contactEntity);
            // check for logged user is for confidence that data changes comes from UI, not from sync process.
            if ($magentoCustomer && $this->securityFacade->hasLoggedUser()) {
                $this->schedulerServiceLink->getService()->schedule(
                    $magentoCustomer->getChannel(),
                    'customer',
                    ['id' => $magentoCustomer->getId()]
                );
            }

            $this->processedIds[] = $contactEntity->getId();
        }

    }
}
