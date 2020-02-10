<?php

namespace Oro\Bundle\MagentoBundle\Acl\Voter;

use Oro\Bundle\MagentoBundle\Entity\IntegrationAwareInterface;
use Oro\Bundle\MagentoBundle\Model\ChannelSettingsProvider;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The base class for security voters for Magento entities involved in two-way synchronization with Magento store.
 */
abstract class AbstractTwoWaySyncVoter extends AbstractEntityVoter
{
    /**
     * @var array
     */
    protected $supportedAttributes = [BasicPermission::CREATE, BasicPermission::EDIT];

    /**
     * @var ObjectIdentityInterface|IntegrationAwareInterface
     */
    protected $object;

    /**
     * @var ChannelSettingsProvider
     */
    protected $settingsProvider;

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $this->object = $object;

        return parent::vote($token, $object, $attributes);
    }

    /**
     * @param ChannelSettingsProvider $settingsProvider
     */
    public function setSettingsProvider($settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityIdentifier($object)
    {
        $identifier = parent::getEntityIdentifier($object);

        // create actions does not contain identifier
        if (!$identifier) {
            return false;
        }

        return $identifier;
    }
}
