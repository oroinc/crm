<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class ContactListener implements OptionalListenerInterface
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
     * @var bool
     */
    protected $enabled = true;

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
     * {@inheritdoc}
     */
    public function setEnabled($enabled = true)
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        if (!$this->enabled) {
            return;
        }

        // check for logged user is for confidence that data changes comes from UI, not from sync process.
        if ($this->securityFacadeLink->getService()->hasLoggedUser()) {
            $em = $event->getEntityManager();
            $this->processUpdates($em);
            $this->processDeletesAndCollections($em);

            while (null !== $magentoCustomer = array_pop($this->processIds)) {
                $this->schedulerServiceLink->getService()->schedule(
                    $magentoCustomer->getChannel(),
                    'customer',
                    ['id' => $magentoCustomer->getId()],
                    false
                );
            }
        }
    }

    /**
     * Process Delete entities and entities Collections
     *
     * @param EntityManager $em
     */
    protected function processDeletesAndCollections(EntityManager $em)
    {
        $unitOfWork  = $em->getUnitOfWork();
        $entities    = $unitOfWork->getScheduledEntityDeletions();
        $collections = array_merge(
            $unitOfWork->getScheduledCollectionUpdates(),
            $unitOfWork->getScheduledCollectionDeletions()
        );

        /** @var PersistentCollection $collection */
        foreach ($collections as $collection) {
            $owner = $collection->getOwner();
            if (!in_array($owner, $entities, true)) {
                $entities[] = $owner;
            }
        }
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
        $unitOfWork = $em->getUnitOfWork();
        $entities   = array_merge(
            $unitOfWork->getScheduledEntityInsertions(),
            $unitOfWork->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
            foreach ($this->checkEntityClasses as $classNames => $classMapConfig) {
                $changed = false;

                if ($entity instanceof $classNames) {
                    if (isset ($classMapConfig['findContactMethod'])) {
                        $method        = $classMapConfig['findContactMethod'];
                        $contactEntity = $entity->$method();

                        if (!empty($contactEntity)) {
                            $changed = true;
                        }
                    } else {
                        $changed       = false;
                        $contactEntity = $entity;

                        $changeSet = $unitOfWork->getEntityChangeSet($contactEntity);
                        foreach (array_keys($changeSet) as $fieldName) {
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
        if ($contactEntity->getId() && !isset($this->processIds[$contactEntity->getId()])) {
            $magentoCustomer = $em->getRepository('OroCRMMagentoBundle:Customer')
                ->findOneBy(['contact' => $contactEntity]);

            if ($this->isTwoWaySyncEnabled($magentoCustomer)) {
                $this->processIds[$contactEntity->getId()] = $magentoCustomer;
            }
        }
    }

    /**
     * @param Customer $magentoCustomer
     *
     * @return bool
     */
    protected function isTwoWaySyncEnabled($magentoCustomer)
    {
        return (
            $magentoCustomer
            && $magentoCustomer->getChannel()->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false)
            && $magentoCustomer->getChannel()->isEnabled()
        );
    }
}
