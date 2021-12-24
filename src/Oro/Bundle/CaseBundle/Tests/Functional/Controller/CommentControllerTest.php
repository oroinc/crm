<?php

namespace Oro\Bundle\CaseBundle\Tests\Functional\Controller;

use Oro\Bundle\CaseBundle\Entity\CaseComment;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Tests\Functional\DataFixtures\LoadCaseEntityData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CommentControllerTest extends WebTestCase
{
    /** @var int */
    private static $caseId;

    /** @var int */
    private static $adminUserId = 1;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([LoadCaseEntityData::class]);
    }

    protected function postFixtureLoad()
    {
        $case = self::getContainer()->get('doctrine')->getRepository(CaseEntity::class)
            ->findOneBy(['subject' => 'Case #1']);

        $this->assertNotNull($case);

        self::$caseId = $case->getId();
    }

    public function testCreateAction(): int
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_case_comment_create', ['caseId' => self::$caseId, '_widgetContainer' => 'dialog']),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 200);

        $form = $crawler->selectButton('Save')->form();
        $form['oro_case_comment_form[message]'] = 'New comment';

        $form->getFormNode()->setAttribute(
            'action',
            $form->getFormNode()->getAttribute('action')
        );

        $crawler = $this->client->submit($form);

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);
        $this->assertEquals(0, $crawler->filter('div')->count());
        self::assertStringContainsString(
            '{"widget":{"trigger":[{"eventBroker":"widget","name":"formSave","args":[',
            $this->client->getResponse()->getContent()
        );
        $this->assertEquals(0, $crawler->filter('form')->count());

        /** @var CaseComment $comment */
        $comment = self::getContainer()->get('doctrine')->getRepository(CaseComment::class)
            ->findOneBy(['message' => 'New comment']);

        $this->assertNotNull($comment);

        return $comment->getId();
    }

    /**
     * @depends testCreateAction
     */
    public function testUpdateAction(int $id): int
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_case_comment_update', ['id' => $id, '_widgetContainer' => 'dialog']),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 200);

        $form = $crawler->selectButton('Save')->form();
        $form['oro_case_comment_form[message]'] = 'Updated comment';

        $form->getFormNode()->setAttribute(
            'action',
            $form->getFormNode()->getAttribute('action') . '?_widgetContainer=dialog'
        );

        $crawler = $this->client->submit($form);

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);
        $this->assertEquals(0, $crawler->filter('div')->count());
        self::assertStringContainsString(
            '{"widget":{"trigger":[{"eventBroker":"widget","name":"formSave","args":[',
            $this->client->getResponse()->getContent()
        );
        $this->assertEquals(0, $crawler->filter('form')->count());

        return $id;
    }

    /**
     * @depends testUpdateAction
     */
    public function testCommentsListAction(int $id): void
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_case_comment_list', ['id' => self::$caseId]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $comments = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(4, $comments);

        /** @var CaseComment $comment */
        $comment = self::getContainer()->get('doctrine')->getRepository(CaseComment::class)
            ->find($id);
        $avatarFile = $comment->getOwner()->getAvatar();
        $userAvatar =  self::getContainer()->get('oro_attachment.manager')
            ->getFilteredImageUrl($avatarFile, 'avatar_xsmall');
        $userAvatarWebp =  self::getContainer()->get('oro_attachment.manager')
            ->getFilteredImageUrl($avatarFile, 'avatar_xsmall', 'webp');

        self::getContainer()->get('doctrine.orm.entity_manager')->refresh($comment);

        $this->assertNotNull($comment);

        $this->assertArrayIntersectEquals(
            [
                'id'            => $comment->getId(),
                'message'       => $comment->getMessage(),
                'briefMessage'  => $comment->getMessage(),
                'public'        => $comment->isPublic(),
                'createdAt'     => $this->getContainer()->get('oro_locale.formatter.date_time')
                    ->format($comment->getCreatedAt()),
                'updatedAt'     => $this->getContainer()->get('oro_locale.formatter.date_time')
                    ->format($comment->getUpdatedAt()),
                'permissions'   => [
                    'edit'          => true,
                    'delete'        => true,
                ],
                'createdBy'         => [
                    'id'            => self::$adminUserId,
                    'url'           => $this->getContainer()->get('router')
                        ->generate('oro_user_view', ['id' => self::$adminUserId]),
                    'fullName'      => 'John Doe',
                    'avatarPicture' => [
                        'src' => $userAvatar,
                        'sources' => [
                            [
                                'srcset' => $userAvatarWebp,
                                'type' => 'image/webp'
                            ]
                        ],
                    ],
                    'permissions'   => [
                        'view'          => true,
                    ],
                ]
            ],
            $comments[0]
        );
    }

    public function testCommentsWidgetAction(): void
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_case_widget_comments', ['id' => self::$caseId, '_widgetContainer' => 'widget']),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($response, 200);

        $crawler = $this->client->getCrawler();
        $this->assertEquals(1, $crawler->filter('div.widget-content div#comment-list')->count());
    }
}
