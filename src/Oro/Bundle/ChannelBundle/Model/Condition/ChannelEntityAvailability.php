<?php

namespace Oro\Bundle\ChannelBundle\Model\Condition;

use Oro\Component\Action\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;

class ChannelEntityAvailability extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;

    /** @var  Channel */
    protected $channel;

    /** @var  Array */
    protected $entities;

    /** @var StateProvider */
    protected $stateProvider;

    public function __construct(StateProvider $stateProvider)
    {
        $this->stateProvider = $stateProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'channel_entity_availiable';
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        if (2 === count($options)) {
            $this->channel  = $options[0];
            $this->entities = $options[1];
        } elseif (1 === count($options)) {
            $this->entities = $options[0];
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid options count: %d',
                    count($options)
                )
            );
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isConditionAllowed($context)
    {
        if (null !== $this->channel) {
            /** @var Channel $dataChannel */
            $dataChannel = $this->resolveValue($context, $this->channel, false);
            $entities    = $dataChannel->getEntities();

            $allowed = count(array_intersect($this->entities, $entities)) === count($this->entities);
        } else {
            $allowed = $this->stateProvider->isEntitiesEnabledInSomeChannel($this->entities);
        }

        return $allowed;
    }
}
