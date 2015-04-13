<?php

namespace OroCRM\Bundle\MagentoBundle\Acl\Voter;

use OroCRM\Bundle\MagentoBundle\Entity\IntegrationAwareInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;

use OroCRM\Bundle\MagentoBundle\Model\ChannelSettingsProvider;

abstract class AbstractTwoWaySyncVoter extends AbstractEntityVoter
{
    const ATTRIBUTE_CREATE = 'CREATE';
    const ATTRIBUTE_EDIT = 'EDIT';

    /**
     * @var array
     */
    protected $supportedAttributes = [self::ATTRIBUTE_CREATE, self::ATTRIBUTE_EDIT];

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
