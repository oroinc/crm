<?php

namespace OroCRM\Bundle\ContactBundle\Serializer\Subscriber;

use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\GenericSerializationVisitor;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;

class ContactSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.post_serialize', 'method' => 'onPostSerialize'),
            array('event' => 'serializer.post_deserialize', 'method' => 'onPostDeserialize'),
            array('event' => 'serializer.pre_deserialize', 'method' => 'onPreDeserialize'),
        );
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        if ($event->getObject() instanceof Contact &&
            $event->getContext()->getDepth() <= 1 &&
            in_array('orocrm_contact_export', $event->getContext()->attributes->get('groups')->getOrElse(array()))
        ) {
            /** @var Contact $object */
            $object = $event->getObject();
            /** @var GenericSerializationVisitor $visitor */
            $visitor = $event->getContext()->getVisitor();
            $visitor->addData(
                'emails',
                $this->convertContactEmailsToString($object->getEmails())
            );
        }
    }

    /**
     * @param ContactEmail[] $contactEmails
     * @return string
     */
    protected function convertContactEmailsToString($contactEmails)
    {
        $orderedEmails = array();
        foreach ($contactEmails as $contactEmail) {
            if ($contactEmail->isPrimary()) {
                array_unshift($orderedEmails, $contactEmail->getEmail());
            } else {
                array_push($orderedEmails, $contactEmail->getEmail());
            }
        }
        return implode(',', $orderedEmails);
    }

    public function onPostDeserialize(ObjectEvent $event)
    {
        $s = 1;
//        if ($event->getObject() instanceof Contact &&
//            $event->getContext()->getDepth() <= 1 &&
//            in_array('orocrm_contact_export', $event->getContext()->attributes->get('groups')->getOrElse(array()))
//        ) {
//            /** @var Contact $object */
//            $object = $event->getObject();
//            /** @var GenericSerializationVisitor $visitor */
//            $visitor = $event->getContext()->getVisitor();
//            $visitor->addData(
//                'emails',
//                $this->convertContactEmailsToString($object->getEmails())
//            );
//        }
    }
//
//    /**
//     * @param ContactEmail[] $contactEmails
//     * @return string
//     */
//    protected function convertContactEmailsToString($contactEmails)
//    {
//        $orderedEmails = array();
//        foreach ($contactEmails as $contactEmail) {
//            if ($contactEmail->isPrimary()) {
//                array_unshift($orderedEmails, $contactEmail->getEmail());
//            } else {
//                array_push($orderedEmails, $contactEmail->getEmail());
//            }
//        }
//        return implode(',', $orderedEmails);
//    }

    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        $s = 1;
//        if ($event->getObject() instanceof Contact &&
//            $event->getContext()->getDepth() <= 1 &&
//            in_array('orocrm_contact_export', $event->getContext()->attributes->get('groups')->getOrElse(array()))
//        ) {
//            /** @var Contact $object */
//            $object = $event->getObject();
//            /** @var GenericSerializationVisitor $visitor */
//            $visitor = $event->getContext()->getVisitor();
//            $visitor->addData(
//                'emails',
//                $this->convertContactEmailsToString($object->getEmails())
//            );
//        }
    }
//
//    /**
//     * @param ContactEmail[] $contactEmails
//     * @return string
//     */
//    protected function convertContactEmailsToString($contactEmails)
//    {
//        $orderedEmails = array();
//        foreach ($contactEmails as $contactEmail) {
//            if ($contactEmail->isPrimary()) {
//                array_unshift($orderedEmails, $contactEmail->getEmail());
//            } else {
//                array_push($orderedEmails, $contactEmail->getEmail());
//            }
//        }
//        return implode(',', $orderedEmails);
//    }
}
