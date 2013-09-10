<?php

namespace OroCRM\Bundle\ContactBundle\Serializer\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;

use JMS\Serializer\GenericSerializationVisitor;
use Oro\Bundle\UserBundle\Entity\User;

use Doctrine\Common\Collections\Collection;

class ContactEmailsHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => 'ArrayCollection<OroCRM\Bundle\ContactBundle\Entity\ContactEmail>',
                'format' => 'array',
                'method' => 'serializeContactEmails',
            ),
        );
    }

    public function serializeContactEmails(
        GenericSerializationVisitor $visitor,
        Collection $contactEmails,
        array $type,
        Context $context
    ) {
        $s = 1;
    }
}
