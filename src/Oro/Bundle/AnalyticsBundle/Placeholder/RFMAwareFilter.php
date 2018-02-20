<?php

namespace Oro\Bundle\AnalyticsBundle\Placeholder;

use Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class RFMAwareFilter
{
    /**
     * @var string
     */
    protected $interface;

    /**
     * @param string $interface
     */
    public function __construct($interface)
    {
        $this->interface = $interface;
    }

    /**
     * @param Channel $entity
     *
     * @return bool
     */
    public function isApplicable($entity)
    {
        if (!$entity instanceof Channel) {
            return false;
        }

        $customerIdentity = $entity->getCustomerIdentity();

        if (empty($customerIdentity)) {
            return false;
        }

        return in_array($this->interface, class_implements($customerIdentity), true);
    }

    /**
     * @param Channel $entity
     *
     * @return bool
     */
    public function isViewApplicable($entity)
    {
        $isApplicable = $this->isApplicable($entity);

        if ($isApplicable) {
            $data = $entity->getData();
            if (empty($data[RFMAwareInterface::RFM_STATE_KEY])) {
                return false;
            }

            return filter_var($data[RFMAwareInterface::RFM_STATE_KEY], FILTER_VALIDATE_BOOLEAN);
        }

        return $isApplicable;
    }
}
