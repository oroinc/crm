<?php

namespace Oro\Bundle\MagentoBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Provides applicable subscription permissions (subcsribe, unsubscribe).
 */
class NewsletterSubscriberPermissionProvider extends AbstractTwoWaySyncActionPermissionProvider
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @return NewsletterSubscriberPermissionProvider
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;

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
        $channelId = $this->getChannelId($record);

        if (!$channelId) {
            return false;
        }

        return $this->channelSettingsProvider->isChannelApplicable($channelId, $checkExtension);
    }

    /**
     * @param ResultRecordInterface $record
     *
     * @return int|null
     */
    protected function getChannelId(ResultRecordInterface $record)
    {
        $channelId = $record->getValue(self::CHANNEL_KEY);
        if (!$channelId) {
            $customer = $record->getValue(self::CUSTOMER);
            if ($customer instanceof Customer || $customer instanceof NewsletterSubscriber) {
                $channel = $customer->getChannel();
                if ($channel) {
                    $channelId = $customer->getChannel()->getId();
                }
            }
        }

        return $channelId;
    }

    /**
     * @return bool
     */
    protected function isSubscribeGranted()
    {
        if ($this->subscribeGranted === null) {
            $this->subscribeGranted = $this->authorizationChecker
                ->isGranted('oro_magento_newsletter_subscriber_subscribe_customer');
        }

        return $this->subscribeGranted;
    }

    /**
     * @return bool
     */
    protected function isUnsubscribeGranted()
    {
        if ($this->unsubscribeGranted === null) {
            $this->unsubscribeGranted = $this->authorizationChecker
                ->isGranted('oro_magento_newsletter_subscriber_unsubscribe_customer');
        }

        return $this->unsubscribeGranted;
    }
}
