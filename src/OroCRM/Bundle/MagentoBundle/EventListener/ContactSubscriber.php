<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactSubscriber implements EventSubscriber
{
    /**
     * @var ServiceLink
     */
    protected $schedulerServiceLink;

    /**
     * @var ServiceLink
     */
    protected $securityFacadeLink;

    /**
     * Entities we must process
     *
     * @var array
     */
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
    protected $processIds = [];

    /**
     * @param ServiceLink $securityFacadeLink
     * @param ServiceLink $schedulerServiceLink
     */
    public function __construct(ServiceLink $securityFacadeLink, ServiceLink $schedulerServiceLink)
    {
        $this->schedulerServiceLink = $schedulerServiceLink;
        $this->securityFacadeLink       = $securityFacadeLink;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            // @codingStandardsIgnoreStart
            Events::postFlush,
            Events::onFlush
            // @codingStandardsIgnoreEnd
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        $this->processUpdates($em);
        $this->processDeletes($em);
    }

    /**
     * {@inheritdoc}
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        foreach ($this->processIds as $magentoCustomer) {
            $this->schedulerServiceLink->getService()->schedule(
                $magentoCustomer->getChannel(),
                'customer',
                ['id' => $magentoCustomer->getId()]
            );
        }
    }

    /**
     * Process Delete entities
     *
     * @param EntityManager $em
     */
    protected function processDeletes(EntityManager $em)
    {
        $unitOfWork      = $em->getUnitOfWork();
        $entities = $unitOfWork->getScheduledEntityDeletions();
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
        $unitOfWork      = $em->getUnitOfWork();
        $entities = array_merge(
            $unitOfWork->getScheduledEntityInsertions(),
            $unitOfWork->getScheduledEntityUpdates()
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

                        $chaneSet = $unitOfWork->getEntityChangeSet($contactEntity);
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
        // check for logged user is for confidence that data changes comes from UI, not from sync process.
        if ($contactEntity->getId()
            && !isset($this->processIds[$contactEntity->getId()])
            && $this->securityFacadeLink->getService()->hasLoggedUser()
        ) {
            $magentoCustomer = $em->getRepository('OroCRMMagentoBundle:Customer')
                ->findOneBy(['contact' => $contactEntity]);
            if ($magentoCustomer) {
                $this->processIds[$contactEntity->getId()] = $magentoCustomer;
            }
        }
    }
}
