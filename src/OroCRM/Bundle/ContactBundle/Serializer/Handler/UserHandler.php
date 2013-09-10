<?php

namespace OroCRM\Bundle\ContactBundle\Serializer\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;

use Oro\Bundle\ImportExportBundle\Serializer\ArraySerializationVisitor;
use Oro\Bundle\UserBundle\Entity\User;

class UserHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => 'Oro\Bundle\UserBundle\Entity\Role',
                'format' => 'array',
                'method' => 'serializeUser',
            ),
        );
    }

    public function serializeUser(ArraySerializationVisitor $visitor, User $user, array $type, Context $context)
    {
        return array(
            $user->getFirstname(),
            $user->getLastname(),
        );
    }
}
