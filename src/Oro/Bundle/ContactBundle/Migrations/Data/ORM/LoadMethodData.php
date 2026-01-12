<?php

namespace Oro\Bundle\ContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Method;

/**
 * Loads predefined contact method data during database initialization.
 */
class LoadMethodData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = array(
        'phone' => 'Phone',
        'email' => 'Email',
        'mail'  => 'Mail',
    );

    #[\Override]
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $methodName => $methodLabel) {
            $method = new Method($methodName);
            $method->setLabel($methodLabel);
            $manager->persist($method);
        }

        $manager->flush();
    }
}
