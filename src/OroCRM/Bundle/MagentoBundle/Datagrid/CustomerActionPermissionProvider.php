<?php

namespace OroCRM\Bundle\MagentoBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class CustomerActionPermissionProvider extends NewsletterSubscriberPermissionProvider
{
    /**
     * @param ResultRecordInterface $record
     * @param array $actions
     *
     * @return array
     */
    public function getCustomerActionsPermissions(ResultRecordInterface $record, array $actions)
    {
        $isTwoWaySyncEnabled = $this->isTwoWaySyncEnable($record);
        $permissions = parent::getActionsPermissions($record, $actions);

        if (array_key_exists('update', $permissions)) {
            $permissions['update'] = $isTwoWaySyncEnabled;
        }

        return $permissions;
    }
}
