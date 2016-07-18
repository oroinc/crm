<?php

namespace OroCRM\Bundle\MagentoBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class NewsletterSubscriberPermissionProvider extends AbstractTwoWaySyncActionPermissionProvider
{
    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var bool|null
     */
    protected $subscribeGranted;

    /**
     * @var bool|null
     */
    protected $unsubscribeGranted;

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
            $permissions['subscribe'] = $this->isSubscribeGranted() && $isActionAllowed && !$isSubscribed;
        }

        if (array_key_exists('unsubscribe', $permissions)) {
            $permissions['unsubscribe'] = $this->isUnsubscribeGranted() && $isActionAllowed && $isSubscribed;
        }

        return $permissions;
    }

    /**
     * @param SecurityFacade $securityFacade
     * @return NewsletterSubscriberPermissionProvider
     */
    public function setSecurityFacade(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;

        return $this;
    }

    /**
     * Check if channel integration is applicable for magento customer.
     * Get channel integration id from customer channel if it's does not exist in grid record
     *
     * @param ResultRecordInterface $record
     * @param bool $checkExtension
     *
     * @return bool
     */
    protected function isChannelApplicable(ResultRecordInterface $record, $checkExtension = true)
    {
        $channelId = $record->getValue(self::CHANNEL_KEY);
        if (!$channelId) {
            $customer = $record->getValue(self::CUSTOMER);
            if ($customer instanceof Customer || $customer instanceof NewsletterSubscriber) {
                $channel = $customer->getChannel();
                if ($channel) {
                    $channelId = $customer->getChannel()->getId();
                } else {
                    return false;
                }
            }
        }

        return $this->channelSettingsProvider->isChannelApplicable($channelId, $checkExtension);
    }

    /**
     * @return bool
     */
    protected function isSubscribeGranted()
    {
        if ($this->subscribeGranted === null) {
            $this->subscribeGranted = $this->securityFacade
                ->isGranted('orocrm_magento_newsletter_subscriber_subscribe_customer');
        }

        return $this->subscribeGranted;
    }

    /**
     * @return bool
     */
    protected function isUnsubscribeGranted()
    {
        if ($this->unsubscribeGranted === null) {
            $this->unsubscribeGranted = $this->securityFacade
                ->isGranted('orocrm_magento_newsletter_subscriber_unsubscribe_customer');
        }

        return $this->unsubscribeGranted;
    }
}
