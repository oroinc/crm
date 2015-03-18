<?php

namespace OroCRM\Bundle\MagentoBundle\Acl\Voter;

use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class TwoWaySyncVoter extends AbstractEntityVoter
{
    const ATTRIBUTE_CREATE = 'CREATE';
    const ATTRIBUTE_EDIT = 'EDIT';

    /**
     * @var array
     */
    protected $supportedAttributes = [self::ATTRIBUTE_CREATE, self::ATTRIBUTE_EDIT];

    /**
     * @var string
     */
    protected $channelClassName;

    /**
     * @var Channel[]
     */
    protected $channels;

    /**
     * @var Customer|ObjectIdentityInterface
     */
    protected $object;

    /**
     * @param string $channelClassName
     */
    public function setChannelClassName($channelClassName)
    {
        $this->channelClassName = $channelClassName;
    }

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $this->object = $object;

        return parent::vote($token, $object, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if (is_a($this->object, $this->className, true)) {
            $syncSettings = $this->object->getChannel()->getSynchronizationSettings();
            $isTwoWaySyncEnabled = (bool)$syncSettings->offsetGet('isTwoWaySyncEnabled');
            if (!$isTwoWaySyncEnabled) {
                return self::ACCESS_DENIED;
            }
        }

        if ($this->object instanceof ObjectIdentityInterface) {
            $isTwoWaySyncEnabled = false;
            foreach ($this->getChannels() as $channel) {
                $syncSettings = $channel->getSynchronizationSettings();
                $isTwoWaySyncEnabled = $isTwoWaySyncEnabled || (bool)$syncSettings->offsetGet('isTwoWaySyncEnabled');
            }

            if (!$isTwoWaySyncEnabled) {
                return self::ACCESS_DENIED;
            }
        }

        return self::ACCESS_ABSTAIN;
    }

    /**
     * @return Channel[]
     */
    protected function getChannels()
    {
        if (!$this->channelClassName) {
            throw new \InvalidArgumentException('Channel class is missing');
        }

        if (!is_array($this->channels)) {
            $this->channels = $this->doctrineHelper
                ->getEntityRepository($this->channelClassName)
                ->findBy(['type' => ChannelType::TYPE]);
        }

        return $this->channels;
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
