<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Provider;

use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;

use OroCRM\Bundle\ContactBundle\Provider\EmailRecipientsProvider;

class EmailRecipientsProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $registry;
    protected $aclHelper;
    protected $nameFormatter;

    protected $emailRecipientsProvider;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->nameFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailRecipientsProvider = new EmailRecipientsProvider(
            $this->registry,
            $this->aclHelper,
            $this->nameFormatter
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetRecipients(EmailRecipientsProviderArgs $args, array $resultEmails)
    {
        $this->nameFormatter->expects($this->once())
            ->method('getFormattedNameDQL')
            ->with('c', 'OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->will($this->returnValue('u.name'));

        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->once())
            ->method('setMaxResults')
            ->will($this->returnSelf());

        $userRepository = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Repository\ContactRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $userRepository->expects($this->once())
            ->method('getPrimaryEmailsQb')
            ->will($this->returnValue($qb));

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMContactBundle:Contact')
            ->will($this->returnValue($userRepository));

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getResult'])
            ->getMockForAbstractclass();
        $query->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($resultEmails));

        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->will($this->returnValue($query));

        $this->emailRecipientsProvider->getRecipients($args);
    }

    public function dataProvider()
    {
        return [
            [
                new EmailRecipientsProviderArgs(null, null, 1),
                [
                    [
                        'name'  => 'Recipient <recipient@example.com>',
                        'email' => 'recipient@example.com',
                    ],
                ],
            ],
        ];
    }
}
