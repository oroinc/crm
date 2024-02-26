<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroSalesBundle_Entity_Customer;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;

/**
 * Entity represents Sales Customer and handles all related mappings
 *
 *
 * @method Customer supportCustomerTarget($targetClass)
 * @method Customer getCustomerTarget()
 * @method Customer setCustomerTarget()
 * @mixin OroSalesBundle_Entity_Customer
 */
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table('orocrm_sales_customer')]
#[ORM\HasLifecycleCallbacks]
#[Config(mode: 'hidden')]
class Customer implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Account::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['importexport' => ['short' => true]])]
    protected ?Account $account = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    /**
     * @param Account     $account
     * @param object|null $target
     *
     * @return $this
     */
    public function setTarget(Account $account, $target = null)
    {
        $this->account = $account;
        $this->setCustomerTarget($target);

        return $this;
    }

    /**
     * Returns Customer's associated target if it set or Account otherwise
     *
     * @return Account|object
     */
    public function getTarget()
    {
        return $this->getCustomerTarget() ?: $this->account;
    }
}
