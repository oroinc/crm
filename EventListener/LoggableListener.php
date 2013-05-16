<?php

namespace Oro\Bundle\DataAuditBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Gedmo\Loggable\LoggableListener as BaseListener;
use Gedmo\Loggable\Mapping\Event\LoggableAdapter;
use Gedmo\Tool\Wrapper\AbstractWrapper;

use Oro\Bundle\DataAuditBundle\Entity\Log;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;

class LoggableListener extends BaseListener
{
    /**
     * Stack of logged flexible entities
     *
     * @var array
     */
    protected $loggedObjects = array();

    /**
     * Create a new Log instance
     *
     * @param string $action
     * @param object $object
     * @param LoggableAdapter $ea
     */
    protected function createLogEntry($action, $object, LoggableAdapter $ea)
    {
        $om   = $ea->getObjectManager();
        $uow  = $om->getUnitOfWork();
        $user = $om->getRepository('OroUserBundle:User')->findOneBy(array('username' => $this->username));

        if (!$user) {
            return;
        }

        $wrapped = AbstractWrapper::wrap($object, $om);
        $meta    = $wrapped->getMetadata();

        if ($config = $this->getConfiguration($om, $meta->name)) {
            $logEntryClass = $this->getLogEntryClass($ea, $meta->name);
            $logEntryMeta  = $om->getClassMetadata($logEntryClass);
            $logEntry      = $logEntryMeta->newInstance();

            // do not store log entries for flexible attributes - add them to a parent entity instead
            if ($object instanceof AbstractEntityFlexibleValue) {
                if (!$this->logFlexible($object, $ea)) {
                    $objectMeta = $om->getClassMetadata(get_class($object));

                    // if no "parent" object has been saved previously - get it from attribute and save it's log
                    if ($objectMeta->reflFields['entity']->getValue($object) instanceof AbstractEntityFlexible) {
                        $this->createLogEntry($action, $objectMeta->reflFields['entity']->getValue($object), $ea);
                    }

                    $this->logFlexible($object, $ea);
                }

                return;
            }

            $logEntry->setAction($action);
            $logEntry->setUsername($this->username);
            $logEntry->setObjectClass($meta->name);
            $logEntry->setLoggedAt();
            $logEntry->setUser($user);

            // check for the availability of the primary key
            $objectId = $wrapped->getIdentifier();

            if (!$objectId && $action === self::ACTION_CREATE) {
                $this->pendingLogEntryInserts[spl_object_hash($object)] = $logEntry;
            }

            $logEntry->setObjectId($objectId);

            $newValues = array();

            if ($action !== self::ACTION_REMOVE && isset($config['versioned'])) {
                foreach ($ea->getObjectChangeSet($uow, $object) as $field => $changes) {
                    if (!in_array($field, $config['versioned'])) {
                        continue;
                    }

                    // fix issues with DateTime
                    if ($changes[0] == $changes[1]) {
                        continue;
                    }

                    $value = $changes[1];

                    if ($meta->isSingleValuedAssociation($field) && $value) {
                        $oid          = spl_object_hash($value);
                        $wrappedAssoc = AbstractWrapper::wrap($value, $om);
                        $value        = $wrappedAssoc->getIdentifier(false);

                        if (!is_array($value) && !$value) {
                            $this->pendingRelatedObjects[$oid][] = array(
                                'log'   => $logEntry,
                                'field' => $field
                            );
                        }
                    }

                    $newValues[$field] = array(
                        'old' => $changes[0],
                        'new' => $value,
                    );
                }

                $logEntry->setData($newValues);
            }

            if ($action === self::ACTION_UPDATE && 0 === count($newValues) && !($object instanceof AbstractEntityFlexible)) {
                return;
            }

            $version = 1;

            if ($action !== self::ACTION_CREATE) {
                $version = $ea->getNewVersion($logEntryMeta, $object);

                if (empty($version)) {
                    // was versioned later
                    $version = 1;
                }
            }

            $logEntry->setVersion($version);

            $this->prePersistLogEntry($logEntry, $object);

            $om->persist($logEntry);
            $uow->computeChangeSet($logEntryMeta, $logEntry);

            // save logged data for possible future handling of flexible attributes
            if ($object instanceof AbstractEntityFlexible) {
                $this->loggedObjects[] = array(
                    'object' => $object,
                    'log'    => $logEntry,
                    'meta'   => $logEntryMeta,
                );
            }
        }
    }

    /**
     * Add flexible attribute log to a parent entity's log entry
     *
     * @param  AbstractEntityFlexibleValue $object
     * @param  LoggableAdapter             $ea
     * @return boolean True if value has been saved, false otherwise
     */
    protected function logFlexible(AbstractEntityFlexibleValue $object, LoggableAdapter $ea)
    {
        $om   = $ea->getObjectManager();
        $uow  = $om->getUnitOfWork();

        foreach ($this->loggedObjects as &$lo) {
            foreach ($lo['object']->getValues() as $value) {
                if ($value == $object) {
                    $logEntry = $lo['log'];
                    $changes  = current($ea->getObjectChangeSet($uow, $object));
                    $data     = array_merge(
                        $logEntry->getData(),
                        array(
                            $object->getAttribute()->getCode() => array(
                                'old' => $changes[0],
                                'new' => $value->getData(),
                            )
                        )
                    );

                    $logEntry->setData($data);

                    $om->persist($logEntry);
                    $uow->recomputeSingleEntityChangeSet($lo['meta'], $logEntry);

                    return true;
                }
            }
        }

        return false;
    }
}
