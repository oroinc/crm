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
    }

    protected function postFixtureLoad()
    {
        parent::postFixtureLoad();

        $this->subscriber = $this->getReference('newsletter_subscriber');
    }

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
        $this->assertContains($this->subscriber->getStatus(), $result->getContent());
        $this->assertContains($this->subscriber->getEmail(), $result->getContent());
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
                        'status' => 1,
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
                        'status' => 1,
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
                        'magento-newsletter-subscriber-grid[_filter][status][value]' => '2'
                    ],
                    'assert' => [],
                    'expectedResultCount' => 0
                ]
            ]
        ];
    }
}
