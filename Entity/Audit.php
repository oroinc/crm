<?php

namespace Oro\Bundle\DataAuditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

use JMS\Serializer\Annotation\Type;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="oro_audit")
 */
class Audit extends AbstractLogEntry
{
    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $action
     *
     * @ORM\Column(type="string", length=8)
     */
    protected $action;

    /**
     * @var string $loggedAt
     *
     * @ORM\Column(name="logged_at", type="datetime")
     */
    protected $loggedAt;

    /**
     * @var string $objectId
     *
     * @ORM\Column(name="object_id", type="integer", length=32, nullable=true)
     */
    protected $objectId;

    /**
     * @var string $objectClass
     *
     * @ORM\Column(name="object_class", type="string", length=255)
     */
    protected $objectClass;

    /**
     * @var string $objectName
     *
     * @ORM\Column(name="object_name", type="string", length=255)
     */
    protected $objectName;

    /**
     * @var integer $version
     *
     * @ORM\Column(type="integer")
     */
    protected $version;

    /**
     * @var string $data
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected $data;

    /**
     * @var string $username
     */
    protected $username;

    /**
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Type("string")
     */
    protected $user;

    /**
     * Set user
     *
     * @param  User  $user
     * @return Audit
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get object name
     *
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Set object name
     *
     * @param  string $objectName
     * @return Audit
     */
    public function setObjectName($objectName)
    {
        $this->objectName = $objectName;

        return $this;
    }
}
