<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\SalesBundle\Model\ExtendCustomer;

/**
 * @ORM\Table("orocrm_sales_customer")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      mode="hidden"
 * )
 * @ORM\Entity(repositoryClass="Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository")
 */
class Customer extends ExtendCustomer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AccountBundle\Entity\Account", cascade="persist")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "short"=true,
     *          }
     *      }
     * )
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
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
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
