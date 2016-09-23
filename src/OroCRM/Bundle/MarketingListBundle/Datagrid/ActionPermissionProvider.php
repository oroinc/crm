<?php

namespace Oro\Bundle\MarketingListBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class ActionPermissionProvider
{
    /**
     * @param ResultRecordInterface $record
     * @param array                 $actions
     *
     * @return array
     */
    public function getMarketingListItemPermissions(ResultRecordInterface $record, array $actions)
    {
        $actions     = array_keys($actions);
        $permissions = array();
        foreach ($actions as $action) {
            $permissions[$action] = true;
        }

        $isSubscribed               = (bool)$record->getValue('subscribed');
        $permissions['subscribe']   = !$isSubscribed;
        $permissions['unsubscribe'] = $isSubscribed;

        return $permissions;
    }
}
