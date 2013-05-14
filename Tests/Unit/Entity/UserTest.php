<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\Status;
use Oro\Bundle\UserBundle\Entity\Email;

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

    public function testConfirmationToken()
    {
        $user  = $this->getUser();
        $token = $user->generateToken();

        $this->assertNotEmpty($token);

        $user->setConfirmationToken($token);

        $this->assertEquals($token, $user->getConfirmationToken());
    }

    public function testHasRole()
    {
        $user    = $this->getUser();
        $role    = new Role(User::ROLE_DEFAULT);
        $newRole = new Role('ROLE_FOO');

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
        $this->assertTrue($user->hasGroup('Users'));

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

    public function testStatuses()
    {
        $user  = $this->getUser();
        $status  = new Status();

        $this->assertNotContains($status, $user->getStatuses());
        $this->assertNull($user->getCurrentStatus());

        $user->addStatus($status);
        $user->setCurrentStatus($status);

        $this->assertContains($status, $user->getStatuses());
        $this->assertEquals($status, $user->getCurrentStatus());

        $user->setCurrentStatus();

        $this->assertNull($user->getCurrentStatus());

        $user->getStatuses()->clear();

        $this->assertNotContains($status, $user->getStatuses());
    }

    public function testEmails()
    {
        $user  = $this->getUser();
        $email  = new Email();

        $this->assertNotContains($email, $user->getEmails());

        $user->addEmail($email);

        $this->assertContains($email, $user->getEmails());

        $user->removeEmail($email);

        $this->assertNotContains($email, $user->getEmails());
    }

    public function testNames()
    {
        $user  = $this->getUser();
        $first = 'James';
        $last  = 'Bond';

        $user->setFirstname($first);
        $user->setLastname($last);

        $this->assertEquals($user->getFullname(), sprintf('%s %s', $first, $last));

        $user->setNameFormat('%last%, %first%');

        $this->assertEquals($user->getFullname(), sprintf('%s, %s', $last, $first));
    }

    public function testDates()
    {
        $user = $this->getUser();
        $now  = new \DateTime('-1 year');

        $user->setBirthday($now);
        $user->setLastLogin($now);

        $this->assertEquals($now, $user->getBirthday());
        $this->assertEquals($now, $user->getLastLogin());
    }

    public function testApi()
    {
        $user = $this->getUser();
        $api  = new UserApi();

        $this->assertNull($user->getApi());

        $user->setApi($api);

        $this->assertEquals($api, $user->getApi());
    }

    public function testImage()
    {
        $user = $this->getUser();

        $this->assertNull($user->getImagePath());

        $user->setImage('test');

        $this->assertNotEmpty($user->getImage());
        $this->assertNotEmpty($user->getImagePath());
        $this->assertNotEmpty($user->getImagePath(true));
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return $this->getMockForAbstractClass('Oro\Bundle\UserBundle\Entity\User');
    }
}
