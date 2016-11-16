<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Entity\AccountAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\SalesBundle\Model\ExtendCustomer;

/**
 * @ORM\Table("orocrm_sales_customer")
 * @ORM\HasLifecycleCallbacks()
 * @Config()
 * @ORM\Entity
 */
class Customer extends ExtendCustomer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Account|null
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AccountBundle\Entity\Account", cascade="persist")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    protected $account;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Account|null
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @return object|null
     */
    public function getTarget()
    {
        return $this->getCustomerTarget() ?: $this->account;
    }

    /**
     * @param object|null $target
     */
    public function setTarget($target)
    {
        if ($target instanceof Account) {
            $this->account = $target;
        } else {
            $this->setCustomerTarget($target);
            if ($target instanceof AccountAwareInterface) {
                $this->account = $target->getAccount();
            }
        }
    }
}
