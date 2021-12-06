<?php

namespace Oro\Bundle\CaseBundle\Tests\Functional\Entity\Manager;

use Oro\Bundle\CaseBundle\Entity\CaseComment;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Entity\CasePriority;
use Oro\Bundle\CaseBundle\Entity\CaseSource;
use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\CaseBundle\Model\CaseEntityManager;
use Oro\Bundle\CaseBundle\Tests\Functional\DataFixtures\LoadCaseEntityData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CaseEntityManagerTest extends WebTestCase
{
    /** @var CaseEntityManager */
    private $manager;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([LoadCaseEntityData::class]);
        $this->manager = $this->getContainer()->get('oro_case.manager');
    }

    public function testCreateCase()
    {
        $case = $this->manager->createCase();
        $this->assertInstanceOf(CaseEntity::class, $case);
        $this->assertEquals(CaseStatus::STATUS_OPEN, $case->getStatus()->getName());
        $this->assertEquals(CaseSource::SOURCE_OTHER, $case->getSource()->getName());
        $this->assertEquals(CasePriority::PRIORITY_NORMAL, $case->getPriority()->getName());
    }

    public function testCreateComment()
    {
        $case = $this->manager->createCase();
        $comment = $this->manager->createComment($case);
        $this->assertInstanceOf(CaseComment::class, $comment);
        $this->assertEquals($case, $comment->getCase());
    }

    /**
     * @dataProvider getCommentsDataProvider
     */
    public function testGetComments($caseSubject, $order, $expectedCommentsMessage)
    {
        $case = self::getContainer()->get('doctrine')->getRepository(CaseEntity::class)
            ->findOneBy(['subject' => $caseSubject]);

        $this->assertNotEmpty($case);

        $comments = $this->manager->getCaseComments($case, $order);

        $this->assertCount(count($expectedCommentsMessage), $comments);
        $this->assertSame(
            $expectedCommentsMessage,
            array_map(
                function (CaseComment $comment) {
                    return $comment->getMessage();
                },
                $comments
            )
        );
    }

    public function getCommentsDataProvider(): array
    {
        return [
            'DESC' => [
                'caseSubject' => 'Case #1',
                'order' => 'DESC',
                'expectedCommentsMessage' => [
                    'Case #1 Comment #3',
                    'Case #1 Comment #2',
                    'Case #1 Comment #1',
                ]
            ],
            'ASC' => [
                'caseSubject' => 'Case #1',
                'order' => 'ASC',
                'expectedCommentsMessage' => [
                    'Case #1 Comment #1',
                    'Case #1 Comment #2',
                    'Case #1 Comment #3',
                ]
            ],
        ];
    }
}
