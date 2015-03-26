<?php

namespace OroCRM\Bundle\MagentoBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class NewsletterSubscriberPermissionProvider extends  AbstractTwoWaySyncActionPermissionProvider
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

        $isTwoWaySyncEnabled = $this->isTwoWaySyncEnable($record);
        $statusId = (int)$record->getValue('newsletterSubscriberStatusId');
        $isSubscribed = $statusId === NewsletterSubscriber::STATUS_SUBSCRIBED;
        $customerId = $record->getValue('customerOriginId');

        // @todo: check extension version

        if (array_key_exists('subscribe', $permissions)) {
            $permissions['subscribe'] = $isTwoWaySyncEnabled && !$isSubscribed && $customerId;
        }

        if (array_key_exists('unsubscribe', $permissions)) {
            $permissions['unsubscribe'] = $isTwoWaySyncEnabled && $isSubscribed && $customerId;
        }

        return $permissions;
    }
}
