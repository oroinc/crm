<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Model\ExtendB2BCustomer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orocrm_sales_b2b_customer")
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "label"="B2BCustomer",
 *              "plural_label"="B2BCustomer",
 *              "icon"="icon-suitcase"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 */
class B2BCustomer extends ExtendB2BCustomer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *  defaultValues={
     *      "importexport"={
     *          "order"=0
     *      }
     *  }
     * )
     */
    protected $id;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=160,
     *          "short"=true
     *      }
     *  }
     * )
     */
    protected $account;

    public function __construct()
    {
        parent::__construct();
    }

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
     * @param Account $account
     * @return B2BCustomer
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
        return $this;
    }
}
