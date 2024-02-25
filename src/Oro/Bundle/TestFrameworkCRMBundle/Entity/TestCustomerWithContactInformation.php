<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\TestFrameworkBundle\Entity\TestFrameworkEntityInterface;

/**
 * TestCustomerWithContactInformation ORM entity.
 */
#[ORM\Entity]
#[ORM\Table(name: 'test_customer_with_contact_info')]
#[Config(
    defaultValues: [
        'entity' => [
            'contact_information' => [
                'email' => [['fieldName' => 'email']],
                'phone' => [['fieldName' => 'phone']]
            ]
        ]
    ]
)]
class TestCustomerWithContactInformation implements
    TestFrameworkEntityInterface,
    ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $phone = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return TestCustomerWithContactInformation
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return TestCustomerWithContactInformation
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }
}
