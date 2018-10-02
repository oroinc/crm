<?php

namespace Oro\Bundle\SalesBundle\Api\Form\EventListener;

use Oro\Bundle\ApiBundle\Form\FormUtil;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Allows to send opprorunity probalility value in range from 0 to 100.
 * It is required to fix BC break for Outlook AddIn.
 */
class NormalizeOpportunityProbabilitySubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => [
                'normalizeProbability',
                10 // convert the value before validation
            ]
        ];
    }

    /**
     * @param FormEvent $formEvent
     */
    public function normalizeProbability(FormEvent $formEvent)
    {
        $form = $formEvent->getForm();
        $opportunity = $formEvent->getData();
        if ($opportunity instanceof Opportunity && $opportunity->getProbability() > 1 && $form->has('probability')) {
            $normalizedProbability = $opportunity->getProbability() / 100;
            $opportunity->setProbability($normalizedProbability);
            if ($normalizedProbability > 1) {
                $constraint = new Range(['min' => 0, 'max' => 100]);
                FormUtil::addFormConstraintViolation(
                    $form->get('probability'),
                    $constraint,
                    str_replace('{{ limit }}', $constraint->max, $constraint->maxMessage)
                );
            }
        }
    }
}
