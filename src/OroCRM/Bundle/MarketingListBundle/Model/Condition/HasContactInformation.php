<?php

namespace OroCRM\Bundle\MarketingListBundle\Model\Condition;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

use Oro\Component\Action\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

/**
 * Check MarketingList for presence of contact information fields of given type
 * Usage:
 *
 * Check marketing list for contact information field of specific type
 *      @has_contact_information:
 *          marketing_list: $marketingList
 *          type: email
 *  Or
 *      @has_contact_information: [$marketingList, "email"]
 *
 * Check marketing list for any contact information field
 *      @has_contact_information:
 *          marketing_list: $marketingList
 *  Or
 *      @has_contact_information: [$marketingList]
 */
class HasContactInformation extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;

    /**
     * @var ContactInformationFieldsProvider
     */
    protected $fieldsProvider;

    /**
     * @var PropertyPathInterface|MarketingList
     */
    protected $marketingList;

    /**
     * @var PropertyPathInterface|string
     */
    protected $type;

    /**
     * @param ContactInformationFieldsProvider $fieldsProvider
     */
    public function __construct(ContactInformationFieldsProvider $fieldsProvider)
    {
        $this->fieldsProvider = $fieldsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'has_contact_information';
    }

    /**
     * {@inheritdoc}
     */
    protected function isConditionAllowed($context)
    {
        $marketingList = $this->resolveValue($context, $this->marketingList, false);
        $type = $this->resolveValue($context, $this->type, false);

        if (!$marketingList instanceof MarketingList) {
            throw new InvalidArgumentException(
                'Option "marketing_list" must be instance of "OroCRM\Bundle\MarketingListBundle\Entity\MarketingList"'
            );
        }

        return (bool)$this->fieldsProvider->getMarketingListTypedFields($marketingList, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (isset($options['marketing_list'])) {
            $this->marketingList = $options['marketing_list'];
        } elseif (isset($options[0])) {
            $this->marketingList = $options[0];
        } else {
            throw new InvalidArgumentException('Option "marketing_list" is required');
        }

        if (isset($options['type'])) {
            $this->type = $options['type'];
        } elseif (isset($options[1])) {
            $this->type = $options[1];
        }

        return $this;
    }
}
