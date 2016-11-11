<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\DataTransformer;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Form\DataTransformer\CustomerToStringTransformer;

class CustomerToStringTransformerTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerToStringTransformer */
    protected $customerToStringTransformer;

    public function setUp()
    {
        $entityToStringTransformer = $this->getMock('Symfony\Component\Form\DataTransformerInterface');
        $entityToStringTransformer->expects($this->any())
            ->method('transform')
            ->will($this->returnValue('parentTransform'));
        $entityToStringTransformer->expects($this->any())
            ->method('reverseTransform')
            ->will($this->returnValue('parentReverseTransform'));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrineHelper->expects($this->any())
            ->method('getEntityManager')
            ->with($this->isInstanceOf(Account::class))
            ->will($this->returnValue($em));

        $this->customerToStringTransformer = new CustomerToStringTransformer(
            $entityToStringTransformer,
            $doctrineHelper
        );
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expectedValue)
    {
        $this->assertEquals(
            $expectedValue,
            $this->customerToStringTransformer->reverseTransform($value)
        );
    }

    public function reverseTransformProvider()
    {
        return [
            'new account' => [
                json_encode(['value' => 'new account']),
                (new Account())
                    ->setName('new account'),
            ],
            'existing account' => [
                json_encode(['entityClass' => Account::class, 'entityId' => 1]),
                'parentReverseTransform',
            ],
        ];
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expectedValue)
    {
        $this->assertEquals(
            $expectedValue,
            $this->customerToStringTransformer->transform($value)
        );
    }

    public function transformProvider()
    {
        $accountIdRef = new \ReflectionProperty(Account::class, 'id');
        $accountIdRef->setAccessible(true);
        $existingAccount = (new Account())
            ->setName('existing account');
        $accountIdRef->setValue($existingAccount, 1);

        return [
            'new account' => [
                (new Account())
                    ->setName('new account'),
                json_encode(['value' => 'new account']),
            ],
            'existing account' => [
                $existingAccount,
                'parentTransform',
            ],
        ];
    }
}
