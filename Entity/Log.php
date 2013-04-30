<?php

namespace Oro\Bundle\DataAuditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="oro_log")
 */
class Log extends AbstractLogEntry
{
    /**
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * Set user
     *
     * @param  User $user
     * @return Log
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
     * Return the role name field
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->user;
    }
}
