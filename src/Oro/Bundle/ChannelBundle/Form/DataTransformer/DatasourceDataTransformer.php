<?php

namespace Oro\Bundle\ChannelBundle\Form\DataTransformer;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

class DatasourceDataTransformer implements DataTransformerInterface
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value || (!$value instanceof Integration)) {
            return null;
        }

        /** @var Integration $value */
        return [
            'type'       => $value->getType(),
            'data'       => null,
            'identifier' => $value,
            'name'       => $value->getName()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        } elseif (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        $data        = $value['data'];
        $integration = $value['identifier'] ? $value['identifier'] : (!empty($data) ? new Integration() : null);

        $form = $this->formFactory->create(
            'oro_integration_channel_form',
            $integration,
            ['csrf_protection' => false, 'disable_customer_datasource_types' => false]
        );

        if (!empty($data)) {
            $form->submit($data);

            if (!$form->isValid()) {
                $errorMessages = array_map(
                    function (FormError $error) {
                        return $error->getMessage();
                    },
                    $form->getErrors()
                );

                throw new \LogicException(sprintf('Malware data received. Errors: %s', implode(', ', $errorMessages)));
            }
        }

        return $integration;
    }
}
