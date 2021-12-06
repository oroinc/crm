<?php

namespace Oro\Bundle\CaseBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Tests\Functional\DataFixtures\LoadCaseEntityData;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CommentControllerTest extends WebTestCase
{
    private array $commentPostData = [
        'message' => 'New comment',
        'owner' => 1,
        'public' => true,
    ];

    /** @var int */
    private static $caseId;

    /** @var int */
    private static $contactId;

    /** @var int */
    private static $adminUserId = 1;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([LoadCaseEntityData::class]);
    }

    protected function postFixtureLoad()
    {
        $case = self::getContainer()->get('doctrine')->getRepository(CaseEntity::class)
            ->findOneBy(['subject' =>  'Case #1']);

        $contact = self::getContainer()->get('doctrine')->getRepository(Contact::class)
            ->findOneBy(['email' => 'daniel.case@example.com']);

        $this->assertNotNull($case);
        $this->assertNotNull($contact);

        self::$caseId = $case->getId();
        self::$contactId = $contact->getId();
    }

    public function testCreate(): int
    {
        $this->client->jsonRequest(
            'POST',
            $this->getUrl('oro_case_api_post_comment', ['id' => self::$caseId]),
            ['comment' => $this->commentPostData],
            $this->generateWsseAuthHeader()
        );

        $response = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $response);
        $this->assertGreaterThan(0, $response['id']);

        return $response['id'];
    }

    /**
     * @depends testCreate
     */
    public function testCget(): int
    {
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_case_api_get_comments', ['id' => self::$caseId]),
            [],
            $this->generateWsseAuthHeader()
        );

        $comments = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(4, $comments);

        $this->assertCommentDataEquals(
            [
                'message' => $this->commentPostData['message'],
                'public' => true,
                'case' => self::$caseId,
                'owner' => self::$adminUserId,
            ],
            $comments[0]
        );

        return $comments[0]['id'];
    }

    /**
     * @depends testCreate
     */
    public function testGet(int $id): array
    {
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_case_api_get_comment', ['id' => $id]),
            [],
            $this->generateWsseAuthHeader()
        );

        $comment = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCommentDataEquals(
            [
                'message' => $this->commentPostData['message'],
                'public' => true,
                'case' => self::$caseId,
                'owner' => self::$adminUserId,
                'contact' => null,
            ],
            $comment
        );

        return $comment;
    }

    /**
     * @depends testGet
     */
    public function testPut(array $originalComment): int
    {
        $id = $originalComment['id'];

        $putData = [
            'message' => 'Updated comment',
            'public' => false,
            'contact' => self::$contactId
        ];

        $this->client->jsonRequest(
            'PUT',
            $this->getUrl('oro_case_api_put_comment', ['id' => $id]),
            ['comment' => $putData],
            $this->generateWsseAuthHeader()
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_case_api_get_comment', ['id' => $id])
        );

        $updatedComment = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertNotEmpty($updatedComment['updatedAt']);

        $expectedComment = array_merge($originalComment, $putData);
        $expectedComment['updatedAt'] = $updatedComment['updatedAt'];

        $this->assertCommentDataEquals($expectedComment, $updatedComment);

        return $id;
    }

    /**
     * @depends testPut
     */
    public function testDelete(int $id)
    {
        $this->client->jsonRequest(
            'DELETE',
            $this->getUrl('oro_case_api_delete_comment', ['id' => $id]),
            [],
            $this->generateWsseAuthHeader()
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_case_api_get_comment', ['id' => $id]),
            [],
            $this->generateWsseAuthHeader()
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }

    private function assertCommentDataEquals(array $expected, array $actual): void
    {
        $this->assertArrayHasKey('id', $actual);
        $this->assertGreaterThan(0, $actual['id']);
        $this->assertIsInt($actual['id']);

        $this->assertArrayHasKey('message', $actual);

        $this->assertArrayHasKey('public', $actual);

        $this->assertArrayHasKey('createdAt', $actual);
        $this->assertNotEmpty($actual['createdAt']);

        $this->assertArrayHasKey('case', $actual);
        $this->assertGreaterThan(0, $actual['case']);
        $this->assertIsInt($actual['case']);

        $this->assertArrayHasKey('owner', $actual);
        $this->assertGreaterThan(0, $actual['owner']);
        $this->assertIsInt($actual['owner']);

        $this->assertArrayIntersectEquals($expected, $actual);
    }
}
