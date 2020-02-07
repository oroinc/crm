<?php

namespace Oro\Bundle\ChannelBundle\Acl\Voter;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Restricts CREATE and DELETE permissions for system channels.
 */
class ChannelVoter extends AbstractEntityVoter
{
    /**
     * @var array
     */
    protected $supportedAttributes = [BasicPermission::CREATE, BasicPermission::DELETE];

    /**
     * @var SettingsProvider
     */
    protected $settingsProvider;

    /**
     * @var Channel
     */
    protected $object;

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $this->object = $object;

        return parent::vote($token, $object, $attributes);
    }

    /**
     * @param SettingsProvider $settingsProvider
     */
    public function setSettingsProvider($settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if (is_a($this->object, $this->className, true)
            && $this->settingsProvider->isSystemChannel($this->object->getChannelType())
        ) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }
}
