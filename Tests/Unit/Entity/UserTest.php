<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Group;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testUsername()
    {
        $user = $this->getUser();
        $name = 'Tony';

        $this->assertNull($user->getUsername());

        $user->setUsername($name);

        $this->assertEquals($name, $user->getUsername());
        $this->assertEquals($name, $user);
    }

    public function testEmail()
    {
        $user = $this->getUser();
        $mail = 'tony@mail.org';

        $this->assertNull($user->getEmail());

        $user->setEmail($mail);

        $this->assertEquals($mail, $user->getEmail());
    }

    public function testIsPasswordRequestNonExpired()
    {
        $user      = $this->getUser();
        $requested = new \DateTime('-10 seconds');

        $user->setPasswordRequestedAt($requested);

        $this->assertSame($requested, $user->getPasswordRequestedAt());
        $this->assertTrue($user->isPasswordRequestNonExpired(15));
        $this->assertFalse($user->isPasswordRequestNonExpired(5));
    }

    public function testIsPasswordRequestAtCleared()
    {
        $user = $this->getUser();
        $requested = new \DateTime('-10 seconds');

        $user->setPasswordRequestedAt($requested);
        $user->setPasswordRequestedAt(null);

        $this->assertFalse($user->isPasswordRequestNonExpired(15));
        $this->assertFalse($user->isPasswordRequestNonExpired(5));
    }

    public function testHasRole()
    {
        $user    = $this->getUser();
        $role    = new Role(User::ROLE_DEFAULT);
        $newRole = new Role('ROLE_FOO');

        $this->assertTrue($user->hasRole(User::ROLE_DEFAULT));
        $this->assertFalse($user->hasRole($newRole->getRole()));

        $user->addRole($role);

        $this->assertTrue($user->hasRole(User::ROLE_DEFAULT));

        $user->addRole($newRole);

        $this->assertTrue($user->hasRole($newRole->getRole()));

        $user->removeRole($newRole);

        $this->assertFalse($user->hasRole($newRole->getRole()));
    }

    public function testGroups()
    {
        $user  = $this->getUser();
        $role  = new Role('ROLE_FOO');
        $group = new Group('Users');

        $group->addRole($role);

        $this->assertNotContains($role, $user->getRoles());

        $user->addGroup($group);

        $this->assertContains($group, $user->getGroups());
        $this->assertContains('Users', $user->getGroupNames());
        $this->assertTrue($user->hasRole($role));

        $user->removeGroup($group);

        $this->assertFalse($user->hasRole($role));
    }

    public function testIsEnabled()
    {
        $user = $this->getUser();

        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertTrue($user->isAccountNonLocked());

        $user->setEnabled(false);

        $this->assertFalse($user->isEnabled());
        $this->assertFalse($user->isAccountNonLocked());
    }

    public function testSerializing()
    {
        $user  = $this->getUser();
        $clone = clone $user;
        $data  = $user->serialize();

        $this->assertNotEmpty($data);

        $user->setPassword('newpass')
             ->setConfirmationToken('token')
             ->setUsername('newname');

        $user->unserialize($data);

        $this->assertEquals($clone, $user);
    }

    public function testPassword()
    {
        $user = $this->getUser();
        $pass = 'anotherPassword';

        $user->setPassword($pass);
        $user->setPlainPassword($pass);

        $this->assertEquals($pass, $user->getPassword());
        $this->assertEquals($pass, $user->getPlainPassword());

        $user->eraseCredentials();

        $this->assertNull($user->getPlainPassword());
    }

    public function testCallbacks()
    {
        $user = $this->getUser();
        $now  = new \DateTime();

        $user->beforeSave();

        $this->assertEquals($now, $user->getCreatedAt());
        $this->assertEquals($now, $user->getUpdatedAt());
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return $this->getMockForAbstractClass('Oro\Bundle\UserBundle\Entity\User');
    }
}
