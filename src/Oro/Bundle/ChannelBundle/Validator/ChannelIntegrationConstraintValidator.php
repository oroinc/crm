<?php

namespace Oro\Bundle\ChannelBundle\Validator;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that a channel datasource is set if the channel integration type is not empty.
 */
class ChannelIntegrationConstraintValidator extends ConstraintValidator
{
    /** @var SettingsProvider */
    protected $provider;

    public function __construct(SettingsProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Channel) {
            throw new UnexpectedTypeException($value, 'Channel');
        }

        $this->validateIntegration($value);
    }

    private function validateIntegration(Channel $channel)
    {
        $channelType = $channel->getChannelType();
        if (!$channelType) {
            return;
        }

        $integrationType = $this->provider->getIntegrationType($channelType);
        if (!$integrationType) {
            return;
        }

        $integration = $channel->getDataSource();
        if ($integration) {
            return;
        }

        $this->context->buildViolation('oro.channel.form.integration_invalid.label')
            ->atPath('dataSource')
            ->addViolation();
    }
}
