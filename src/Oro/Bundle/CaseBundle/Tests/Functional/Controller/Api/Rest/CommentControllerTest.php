<?php

namespace Oro\Bundle\CaseBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CommentControllerTest extends WebTestCase
{
    /**
     * @var array
     */
    protected $commentPostData = [
        'message' => 'New comment',
        'owner' => 1,
        'public' => true,
    ];

    /**
     * @var int
     */
    protected static $caseId;

    /**
     * @var int
     */
    protected static $contactId;

    /**
     * @var int
     */
    protected static $adminUserId = 1;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures(['Oro\Bundle\CaseBundle\Tests\Functional\DataFixtures\LoadCaseEntityData']);
    }

    protected function postFixtureLoad()
    {
        $case = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroCaseBundle:CaseEntity')
            ->findOneBySubject('Case #1');

        $contact = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroContactBundle:Contact')
            ->findOneByEmail('daniel.case@example.com');

        $this->assertNotNull($case);
        $this->assertNotNull($contact);

        self::$caseId = $case->getId();
        self::$contactId = $contact->getId();
    }

    public function testCreate()
    {
        $this->client->request(
            'POST',
            $this->getUrl('oro_case_api_post_comment', ['id' => self::$caseId]),
            ['comment' => $this->commentPostData],
            [],
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
    public function testCget()
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_case_api_get_comments', ['id' => self::$caseId]),
            [],
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
     * @param integer $id
     * @return array
     */
    public function testGet($id)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_case_api_get_comment', ['id' => $id]),
            [],
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
    public function testPut(array $originalComment)
    {
        $id = $originalComment['id'];

        $putData = [
            'message' => 'Updated comment',
            'public' => false,
            'contact' => self::$contactId
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('oro_case_api_put_comment', ['id' => $id]),
            ['comment' => $putData],
            [],
            $this->generateWsseAuthHeader()
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
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
     * @param integer $id
     */
    public function testDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_case_api_delete_comment', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_case_api_get_comment', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }

    protected function assertCommentDataEquals(array $expected, array $actual)
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
