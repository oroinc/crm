<?php

namespace Oro\Bundle\UserBundle\Entity;

use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use FOS\UserBundle\Model\UserInterface;

class UserManager extends BaseUserManager
{
    /**
     * Finds a user by email
     *
     * @param string $email
     * @return UserInterface
     */
    public function findUserByEmail($email)
    {
        return $this->findUserBy(array('email' => $this->canonicalizeEmail($email)));
    }

    /**
     * Finds a user by username
     *
     * @param string $username
     * @return UserInterface
     */
    public function findUserByUsername($username)
    {
        return $this->findUserBy(array('username' => $this->canonicalizeUsername($username)));
    }
}