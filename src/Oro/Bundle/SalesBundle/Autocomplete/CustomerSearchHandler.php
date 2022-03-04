<?php

namespace Oro\Bundle\SalesBundle\Autocomplete;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityNotFoundException;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ActivityBundle\Autocomplete\ContextSearchHandler;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountAutocomplete\ChainAccountAutocompleteProvider;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProviderInterface;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\SearchBundle\Query\Result\Item;

/**
 * Search autocomplete handler for business customer form type
 */
class CustomerSearchHandler extends ContextSearchHandler
{
    const AMOUNT_SEARCH_RESULT = 10;

    /** @var EntityRoutingHelper */
    protected $routingHelper;

    /** @var CustomerIconProviderInterface */
    protected $customerIconProvider;

    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /** @var ChainAccountAutocompleteProvider */
    protected $chainAccountAutocompleteProvider;

    /** @var string */
    protected $query;

    public function setCustomerIconProvider(CustomerIconProviderInterface $customerIconProvider)
    {
        $this->customerIconProvider = $customerIconProvider;
    }

    public function setCustomerConfigProvider(ConfigProvider $customerConfigProvider)
    {
        $this->customerConfigProvider = $customerConfigProvider;
    }

    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    public function setRoutingHelper(EntityRoutingHelper $routingHelper)
    {
        $this->routingHelper  = $routingHelper;
    }

    public function setAccountCustomerManager(AccountCustomerManager $accountCustomerManager)
    {
        $this->accountCustomerManager = $accountCustomerManager;
    }

    public function setChainAccountAutocompleteProvider(
        ChainAccountAutocompleteProvider $chainAccountAutocompleteProvider
    ) {
        $this->chainAccountAutocompleteProvider = $chainAccountAutocompleteProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function search($query, $page, $perPage, $searchById = false)
    {
        $this->query = trim($query);
        $result = parent::search($query, $page, $perPage, $searchById);
        $result['more'] = false;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertItems(array $items)
    {
        $groupedItems = $this->groupItemsByEntityName($items);
        $customers = $this->loadCustomersByGroupedItems($groupedItems);

        $results = [];
        if (count($customers) === 1 && $customers[0] instanceof Account) {
            foreach ($customers as $customer) {
                $results = $this->addCustomerInResults(
                    $customer,
                    $results,
                    $groupedItems
                );
            }
        } else {
            foreach ($customers as $customer) {
                try {
                    $account = $this->detectAccount($customer);
                    $identifierAccount = $this->doctrineHelper->getSingleEntityIdentifier($account);
                    $results = $this->addAccountInResults($results, $account, $groupedItems);

                    if (!$this->customerIsAccount($customer)) {
                        $results = $this->addCustomerWithAccountInResults(
                            $customer,
                            $identifierAccount,
                            $results,
                            $groupedItems
                        );
                    }
                } catch (EntityNotFoundException $e) {
                    // in the $this->detectAccounе used method getAccountCustomerByTarget of AccountCustomerManager
                    // which makes search by sales customer. getAccountCustomerByTarget method throws exception
                    // EntityNotFoundException in case when customer was not found and we should skip handling
                    // account for customer
                }
            }

            $results = $this->sortResultsByItemsPriority($results, $items);
        }

        return $results;
    }

    /**
     * @param Item[] $items
     *
     * @return array
     */
    protected function groupItemsByEntityName(array $items)
    {
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item->getEntityName()][$item->getRecordId()] = $item;
        }

        return $grouped;
    }

    /**
     * @param $groupedItems
     *
     * @return array
     */
    protected function loadCustomersByGroupedItems($groupedItems)
    {
        $customers = [];
        foreach ($groupedItems as $entityName => $groupitems) {
            $customers = array_merge(
                $customers,
                $this->objectManager
                    ->getRepository($entityName)
                    ->findBy(['id' => array_keys($groupitems)])
            );
        }

        return $customers;
    }

    /**
     * @param object $customer
     *
     * @return Account
     */
    protected function detectAccount($customer)
    {
        if ($customer instanceof Account) {
            $account = $customer;
        } else {
            $salesCustomer = $this->accountCustomerManager->getAccountCustomerByTarget($customer);
            $account = $salesCustomer->getAccount();
        }

        return $account;
    }

    /**
     * @param $results
     * @param $account
     * @param $groupedItems
     *
     * @return mixed
     */
    protected function addAccountInResults($results, $account, $groupedItems)
    {
        $identifierAccount = $this->doctrineHelper->getSingleEntityIdentifier($account);
        $key = $identifierAccount;

        if (!array_key_exists($key, $results)) {
            $results[$key] = [
                'id' => $key,
                'text' => '',
                'children' => []
            ];
            if (isset($groupedItems[ClassUtils::getClass($account)][$identifierAccount])) {
                $searchItem = $groupedItems[ClassUtils::getClass($account)][$identifierAccount];
            } else {
                $searchItem = null;
            }

            $results[$key]['children'][] = $this->convertItem([
                'searchItem' => $searchItem,
                'entity' => $account
            ]);
        }

        return $results;
    }

    /**
     * @param $customer
     * @param $identifierAccount
     * @param $results
     * @param $groupedItems
     *
     * @return mixed
     */
    protected function addCustomerWithAccountInResults($customer, $identifierAccount, $results, $groupedItems)
    {
        $identifier = $this->doctrineHelper->getSingleEntityIdentifier($customer);
        $searchItem = $groupedItems[ClassUtils::getClass($customer)][$identifier];

        $results[$identifierAccount]['children'][] = $this->convertItem([
            'searchItem' => $searchItem,
            'entity' => $customer
        ]);

        return $results;
    }

    /**
     * @param $customer
     * @param $results
     * @param $groupedItems
     *
     * @return array
     */
    protected function addCustomerInResults($customer, $results, $groupedItems)
    {
        $identifier = $this->doctrineHelper->getSingleEntityIdentifier($customer);
        $searchItem = $groupedItems[ClassUtils::getClass($customer)][$identifier];

        $results[] = $this->convertItem([
            'searchItem' => $searchItem,
            'entity' => $customer
        ]);

        return $results;
    }

    /**
     * @param $customer
     *
     * @return bool
     */
    protected function customerIsAccount($customer)
    {
        return $customer instanceof Account;
    }

    /**
     * @param $results
     * @param $items
     *
     * @return array
     */
    protected function sortResultsByItemsPriority($results, $items)
    {
        $sortedResult = [];

        /** @var Item $item */
        foreach ($items as $item) {
            foreach ($results as &$result) {
                $childrens = $result['children'];

                if (array_key_exists('isAdded', $result) && $result['isAdded']) {
                    continue;
                }

                foreach ($childrens as $children) {
                    if ($children['id'] === $this->generateId($item)) {
                        $sortedResult[] = $result;
                        $result['isAdded'] = true;
                        break;
                    }
                }
            }
        }

        return $sortedResult;
    }

    /**
     * @param Item $item
     *
     * @return string
     */
    protected function generateId(Item $item)
    {
        return json_encode([
            "entityClass" => $item->getEntityName(),
            "entityId" => $item->getRecordId()
        ]);
    }

    protected function detectMatch($text, $data)
    {
        if ($this->isMatch($text, $this->query)) {
            return;
        }

        if (is_array($data)) {
            foreach ($data as $item) {
                foreach ($item as $value) {
                    if ($this->isMatch($value, $this->query)) {
                        return $value;
                    }
                }
            }
        }
    }

    /**
     * @param $value
     * @param $query
     *
     * @return bool
     */
    protected function isMatch($value, $query)
    {
        if (strlen($query) === 0) {
            return false;
        }

        return strpos(strtolower(str_replace("-", "", $value)), strtolower(str_replace("-", "", $query))) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function convertItem($item)
    {
        $entity = $item['entity'];
        $data = $this->chainAccountAutocompleteProvider->getData($entity);

        $identifier = null;
        if (!empty($item['searchItem'])) {
            /** @var Item $searchItem */
            $searchItem = $item['searchItem'];
            $this->dispatcher->dispatch(new PrepareResultItemEvent($searchItem), PrepareResultItemEvent::EVENT_NAME);

            $text = $searchItem->getSelectedData()['name'];
            $className = $searchItem->getEntityName();
            $identifier = $searchItem->getRecordId();

            if (strlen(trim($text)) === 0) {
                $text = $this->translator->trans('oro.entity.item', ['%id%' => $searchItem->getRecordId()]);
            }
        } elseif ($entity instanceof Account) {
            $text = $entity->getName();
            $identifier = $entity->getId();
            $className = ClassUtils::getClass($entity);
        } else {
            $identifier = $this->doctrineHelper->getSingleEntityIdentifier($entity);
            $text = $this->translator->trans('oro.entity.item', ['%id%' => $identifier]);
            $className = ClassUtils::getClass($entity);
        }

        return [
            'id'   => json_encode(
                [
                    'entityClass' => $className,
                    'entityId'    => $identifier,
                ]
            ),
            'text' => $text,
            'label' => $this->getClassLabel($className),
            'icon' => $this->customerIconProvider->getIcon($entity),
            'data' => $data,
            'matchValue' => $this->detectMatch($text, $data)
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSearchAliases()
    {
        $customers = $this->customerConfigProvider->getCustomerClasses();

        return array_values($this->indexer->getEntityAliases($customers));
    }

    /**
     * {@inheritdoc}
     */
    protected function decodeTargets($targetsString)
    {
        return array_map(
            function ($item) {
                $item['entityClass'] = $this->routingHelper->resolveEntityClass($item['entityClass']);

                return $item;
            },
            parent::decodeTargets($targetsString)
        );
    }
}
