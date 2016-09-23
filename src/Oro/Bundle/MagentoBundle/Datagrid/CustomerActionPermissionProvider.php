<?php

namespace Oro\Bundle\MagentoBundle\Datagrid;

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
        $isChannelApplicable = $this->isChannelApplicable($record, false);
        $permissions = parent::getActionsPermissions($record, $actions);

        if (array_key_exists('update', $permissions)) {
            $permissions['update'] = $isChannelApplicable;
        }

        return $permissions;
    }
}
