<?php

namespace Oro\Bundle\CaseBundle\Tests\Functional\Controller;

use Oro\Bundle\CaseBundle\Entity\CaseComment;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CommentControllerTest extends WebTestCase
{
    /**
     * @var int
     */
    protected static $caseId;

    /**
     * @var int
     */
    protected static $adminUserId = 1;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->loadFixtures(['Oro\Bundle\CaseBundle\Tests\Functional\DataFixtures\LoadCaseEntityData']);
    }

    protected function postFixtureLoad()
    {
        $case = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroCaseBundle:CaseEntity')
            ->findOneBySubject('Case #1');

        $this->assertNotNull($case);

        self::$caseId = $case->getId();
    }

    public function testCreateAction()
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
        static::assertStringContainsString(
            '{"widget":{"trigger":[{"eventBroker":"widget","name":"formSave","args":[',
            $this->client->getResponse()->getContent()
        );
        $this->assertEquals(0, $crawler->filter('form')->count());

        /** @var CaseComment $comment */
        $comment = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroCaseBundle:CaseComment')
            ->findOneByMessage('New comment');

        $this->assertNotNull($comment);

        return $comment->getId();
    }

    /**
     * @depends testCreateAction
     */
    public function testUpdateAction($id)
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
        static::assertStringContainsString(
            '{"widget":{"trigger":[{"eventBroker":"widget","name":"formSave","args":[',
            $this->client->getResponse()->getContent()
        );
        $this->assertEquals(0, $crawler->filter('form')->count());

        return $id;
    }

    /**
     * @depends testUpdateAction
     */
    public function testCommentsListAction($id)
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
        $comment = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroCaseBundle:CaseComment')
            ->find($id);
        $userAvatar =  $this->getContainer()
                ->get('oro_attachment.manager')
                ->getFilteredImageUrl($comment->getOwner()->getAvatar(), 'avatar_xsmall');

        $this->getContainer()->get('doctrine.orm.entity_manager')->refresh($comment);

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
                'permissions'   => array(
                    'edit'          => true,
                    'delete'        => true,
                ),
                'createdBy'         => array(
                    'id'            => self::$adminUserId,
                    'url'           => $this->getContainer()->get('router')
                        ->generate('oro_user_view', array('id' => self::$adminUserId)),
                    'fullName'      => 'John Doe',
                    'avatar'        => $userAvatar,
                    'permissions'   => array(
                        'view'          => true,
                    ),
                )
            ],
            $comments[0]
        );
    }

    public function testCommentsWidgetAction()
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
