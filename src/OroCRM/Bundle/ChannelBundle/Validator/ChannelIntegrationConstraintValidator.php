<?php

namespace Oro\Bundle\ChannelBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelIntegrationConstraintValidator extends ConstraintValidator
{
    /** @var SettingsProvider */
    protected $provider;

    /**
     * @param SettingsProvider $provider
     */
    public function __construct(SettingsProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!($value instanceof Channel)) {
            throw new UnexpectedTypeException($value, 'Channel');
        }

        $this->validateIntegration($value);
    }

    /**
     * @param Channel $channel
     */
    protected function validateIntegration(Channel $channel)
    {
        $errorLabel      = 'oro.channel.form.integration_invalid.label';
        $field           = 'dataSource';
        $integrationType = $this->provider->getIntegrationType($channel->getChannelType());

        if (!empty($integrationType)) {
            $integration = $channel->getDataSource();

            if (empty($integration)) {
                $this->context->addViolationAt($field, $errorLabel);
            }
        }
    }
}
