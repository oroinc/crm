<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\CaseBundle\Entity\CaseComment;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @dbReindex
 */
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

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->loadFixtures(['OroCRM\Bundle\CaseBundle\Tests\Functional\DataFixtures\LoadCaseEntityData']);
    }

    protected function postFixtureLoad()
    {
        $case = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroCRMCaseBundle:CaseEntity')
            ->findOneBySubject('Case #1');

        $this->assertNotNull($case);

        self::$caseId = $case->getId();
    }

    public function testCreateAction()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orocrm_case_comment_create', ['caseId' => self::$caseId, '_widgetContainer' => 'dialog']),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 200);

        $form = $crawler->selectButton('Save')->form();
        $form['orocrm_case_comment_form[message]'] = 'New comment';

        /** TODO Change after BAP-1813 */
        $form->getFormNode()->setAttribute(
            'action',
            $form->getFormNode()->getAttribute('action')
        );

        $crawler = $this->client->submit($form);

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);
        $this->assertEquals(1, $crawler->filter('div.widget-content script')->count());
        $this->assertEquals(0, $crawler->filter('form')->count());

        /** @var CaseComment $comment */
        $comment = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroCRMCaseBundle:CaseComment')
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
            $this->getUrl('orocrm_case_comment_update', ['id' => $id, '_widgetContainer' => 'dialog']),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 200);

        $form = $crawler->selectButton('Save')->form();
        $form['orocrm_case_comment_form[message]'] = 'Updated comment';

        /** TODO Change after BAP-1813 */
        $form->getFormNode()->setAttribute(
            'action',
            $form->getFormNode()->getAttribute('action') . '?_widgetContainer=dialog'
        );

        $crawler = $this->client->submit($form);

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);
        $this->assertEquals(1, $crawler->filter('div.widget-content script')->count());
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
            $this->getUrl('orocrm_case_comment_list', ['id' => self::$caseId]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $comments = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(4, $comments);

        /** @var CaseComment $comment */
        $comment = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroCRMCaseBundle:CaseComment')
            ->find($id);

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
                    'avatar'        => null,
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
            $this->getUrl('orocrm_case_widget_comments', ['id' => self::$caseId, '_widgetContainer' => 'widget']),
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
