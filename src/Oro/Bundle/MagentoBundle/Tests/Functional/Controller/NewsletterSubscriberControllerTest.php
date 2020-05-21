<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Job\JobResult;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

/**
 * @dbIsolationPerTest
 */
class NewsletterSubscriberControllerTest extends AbstractController
{
    /**
     * @var NewsletterSubscriber
     */
    protected $subscriber;

    /**
     * @var JobExecutor
     */
    protected $baseJobExecutor;

    /**
     * @var bool
     */
    protected $isRealGridRequest = true;

    /**
     * {@inheritdoc}
     */
    protected function getMainEntityId()
    {
        return $this->subscriber->getid();
    }

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $jobExecutor = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Job\JobExecutor')
            ->disableOriginalConstructor()
            ->getMock();

        $jobResult = new JobResult();
        $jobResult->setSuccessful(true);

        $jobExecutor->expects($this->any())
            ->method('executeJob')
            ->willReturn($jobResult);

        $this->getContainer()->set('oro_importexport.job_executor.test', $jobExecutor);

        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadNewsletterSubscriberData']);

        $this->subscriber = $this->getReference('newsletter_subscriber');
    }

    public function testView()
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_magento_newsletter_subscriber_view', ['id' => $this->getMainEntityId()])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('General Information', $result->getContent());
        static::assertStringContainsString($this->subscriber->getCustomer()->getFirstName(), $result->getContent());
        static::assertStringContainsString($this->subscriber->getCustomer()->getLastName(), $result->getContent());
        static::assertStringContainsString($this->subscriber->getEmail(), $result->getContent());
        static::assertStringContainsString($this->subscriber->getStatus()->getName(), $result->getContent());
    }

    /**
     * @return array
     */
    public function gridProvider()
    {
        return [
            'default' => [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-newsletter-subscriber-grid',
                        'magento-newsletter-subscriber-grid[_sort_by][customerName]' => 'DESC',
                    ],
                    'gridFilters' => [],
                    'assert' => [
                        'channelName' => 'Magento channel',
                        'email' => 'subscriber3@example.com',
                        'status' => 'Unsubscribed',
                        'customerName' => 'John Doe',
                        'customerEmail' => 'test@example.com'
                    ],
                    'expectedResultCount' => 3
                ]
            ],
            'filters' => [
                [
                    'gridParameters' => ['gridName' => 'magento-newsletter-subscriber-grid'],
                    'gridFilters' => [
                        'magento-newsletter-subscriber-grid[_filter][status][value]' => '1'
                    ],
                    'assert' => [
                        'channelName' => 'Magento channel',
                        'email' => 'subscriber3@example.com',
                        'status' => 'Unsubscribed',
                        'customerName' => 'John Doe',
                        'customerEmail' => 'test@example.com'
                    ],
                    'expectedResultCount' => 3
                ]
            ],
            'no result' => [
                [
                    'gridParameters' => ['gridName' => 'magento-newsletter-subscriber-grid'],
                    'gridFilters' => [
                        'magento-newsletter-subscriber-grid[_filter][email][value]' => 'not.exists@example.com'
                    ],
                    'assert' => [],
                    'expectedResultCount' => 0
                ]
            ]
        ];
    }

    /**
     * @depends testView
     */
    public function testUnsubscribe()
    {
        $this->ajaxRequest(
            'POST',
            $this->getUrl('oro_magento_newsletter_subscriber_unsubscribe', ['id' => $this->getMainEntityId()])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertTrue($result['successful']);
    }

    /**
     * @depends testUnsubscribe
     */
    public function testSubscribe()
    {
        $this->ajaxRequest(
            'POST',
            $this->getUrl('oro_magento_newsletter_subscriber_subscribe', ['id' => $this->getMainEntityId()])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertTrue($result['successful']);
    }

    public function testUnsubscribeByCustomer()
    {
        $this->ajaxRequest(
            'POST',
            $this->getUrl(
                'oro_magento_newsletter_subscriber_unsubscribe_customer',
                ['id' => $this->subscriber->getCustomer()->getId()]
            )
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertTrue($result['successful']);
    }

    public function testSubscribeByCustomer()
    {
        $subscriber = $this->getReference('newsletter_subscriber3');
        $this->ajaxRequest(
            'POST',
            $this->getUrl(
                'oro_magento_newsletter_subscriber_subscribe_customer',
                ['id' => $subscriber->getCustomer()->getId()]
            )
        );
        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertTrue($result['successful']);
    }
}
