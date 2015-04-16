<?php

namespace OroCRM\Bundle\MagentoBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class NewsletterSubscriberPermissionProvider extends AbstractTwoWaySyncActionPermissionProvider
{
    /**
     * @param ResultRecordInterface $record
     * @param array $actions
     *
     * @return array
     */
    public function getActionsPermissions(ResultRecordInterface $record, array $actions)
    {
        $actions = array_keys($actions);
        $permissions = [];
        foreach ($actions as $action) {
            $permissions[$action] = true;
        }

        $isChannelApplicable = $this->isChannelApplicable($record);
        $customerId = $record->getValue(self::CUSTOMER_ID);
        $customerOriginId = $record->getValue(self::CUSTOMER_ORIGIN_ID);

        $isActionAllowed = $isChannelApplicable && (($customerOriginId && $customerId) || !$customerId);

        $statusId = (int)$record->getValue('newsletterSubscriberStatusId');
        $isSubscribed = $statusId === NewsletterSubscriber::STATUS_SUBSCRIBED;

        if (array_key_exists('subscribe', $permissions)) {
            $permissions['subscribe'] = $isActionAllowed && !$isSubscribed;
        }

        if (array_key_exists('unsubscribe', $permissions)) {
            $permissions['unsubscribe'] = $isActionAllowed && $isSubscribed;
        }

        return $permissions;
    }
}
