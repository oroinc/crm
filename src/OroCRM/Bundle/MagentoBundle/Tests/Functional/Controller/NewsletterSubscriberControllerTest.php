<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

/**
 * @dbIsolation
 */
class NewsletterSubscriberControllerTest extends AbstractController
{
    /**
     * @var NewsletterSubscriber
     */
    protected $subscriber;

    /**
     * {@inheritdoc}
     */
    protected function getMainEntityId()
    {
        return $this->subscriber->getid();
    }

    protected function setUp()
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());

        $this->loadFixtures(['OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadNewsletterSubscriberData']);

        $this->subscriber = $this->getReference('newsletter_subscriber');
    }

    protected function tearDown()
    {
        // clear DB from separate connection
        $batchJobManager = $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:JobInstance')->execute();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:JobExecution')->execute();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:StepExecution')->execute();

        unset($this->transport);
    }

    /**
     * @param array $requestData
     *
     * @dataProvider gridProvider
     */
    public function testGrid($requestData)
    {
        parent::testGrid($requestData);
    }

    /**
     * @depends testGrid
     */
    public function testView()
    {
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_magento_newsletter_subscriber_view', ['id' => $this->getMainEntityId()])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('General Information', $result->getContent());
        $this->assertContains($this->subscriber->getCustomer()->getFirstName(), $result->getContent());
        $this->assertContains($this->subscriber->getCustomer()->getLastName(), $result->getContent());
        $this->assertContains($this->subscriber->getEmail(), $result->getContent());
        $this->assertContains($this->subscriber->getStatus()->getName(), $result->getContent());
    }

    /**
     * @return array
     */
    public function gridProvider()
    {
        return [
            'default' => [
                [
                    'gridParameters' => ['gridName' => 'magento-newsletter-subscriber-grid'],
                    'gridFilters' => [],
                    'assert' => [
                        'channelName' => 'Magento channel',
                        'email' => 'subscriber@example.com',
                        'status' => 'Subscribed',
                        'customerName' => 'John Doe',
                        'customerEmail' => 'test@example.com'
                    ],
                    'expectedResultCount' => 1
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
                        'email' => 'subscriber@example.com',
                        'status' => 'Subscribed',
                        'customerName' => 'John Doe',
                        'customerEmail' => 'test@example.com'
                    ],
                    'expectedResultCount' => 1
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
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_magento_newsletter_subscriber_unsubscribe', ['id' => $this->getMainEntityId()])
        );

        $this->subscriber = $this->getContainer()->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:NewsletterSubscriber')
            ->find($this->subscriber->getId());

        $this->assertEquals(NewsletterSubscriber::STATUS_UNSUBSCRIBED, $this->subscriber->getStatus()->getId());
    }

    /**
     * @depends testUnsubscribe
     */
    public function testSubscribe()
    {
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_magento_newsletter_subscriber_subscribe', ['id' => $this->getMainEntityId()])
        );

        $this->subscriber = $this->getContainer()->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:NewsletterSubscriber')
            ->find($this->subscriber->getId());

        $this->assertEquals(NewsletterSubscriber::STATUS_SUBSCRIBED, $this->subscriber->getStatus()->getId());
    }

    /**
     * @depends testSubscribe
     */
    public function testUnsubscribeByCustomer()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'orocrm_magento_newsletter_subscriber_unsubscribe_customer',
                ['id' => $this->subscriber->getCustomer()->getId()]
            )
        );

        $this->subscriber = $this->getContainer()->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:NewsletterSubscriber')
            ->find($this->subscriber->getId());

        $this->assertEquals(NewsletterSubscriber::STATUS_UNSUBSCRIBED, $this->subscriber->getStatus()->getId());
    }

    /**
     * @depends testUnsubscribeByCustomer
     */
    public function testSubscribeByCustomer()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'orocrm_magento_newsletter_subscriber_subscribe_customer',
                ['id' => $this->subscriber->getCustomer()->getId()]
            )
        );

        $this->subscriber = $this->getContainer()->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:NewsletterSubscriber')
            ->find($this->subscriber->getId());

        $this->assertEquals(NewsletterSubscriber::STATUS_SUBSCRIBED, $this->subscriber->getStatus()->getId());
    }
}
