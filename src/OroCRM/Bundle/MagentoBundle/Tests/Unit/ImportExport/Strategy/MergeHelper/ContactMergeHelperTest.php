<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy\MergeHelper;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\MergeHelper\ContactMergeHelper;

class ContactMergeHelperTest extends \PHPUnit_Framework_TestCase
{
    const TEST_LOCAL_DATA  = 'data local';
    const TEST_REMOTE_DATA = 'data remote';
    const TEST_DATA        = 'some data';

    /** @var ContactMergeHelper */
    protected $helper;

    public function setUp()
    {
        $this->helper = new ContactMergeHelper();
    }

    public function tearDown()
    {
        unset($this->helper);
    }

    /**
     * @dataProvider scalarDataProvider
     *
     * @param string $syncPriority
     * @param string $remoteData
     * @param string $localData
     * @param string $contactData
     * @param string $expectedValue
     */
    public function testMergeScalarFields($syncPriority, $remoteData, $localData, $contactData, $expectedValue)
    {
        $channel = new Channel();
        $channel->setSyncPriority($syncPriority);
        $remoteCustomer = new Customer();
        $remoteCustomer->setFirstName($remoteData);
        $remoteCustomer->setId(123);
        $localCustomer = new Customer();
        $localCustomer->setFirstName($localData);
        $localCustomer->setChannel($channel);
        $localCustomer->setId(123);
        $contact = new Contact();
        $contact->setFirstName($contactData);
        $contact->setId(123);

        $this->helper->mergeScalars($remoteCustomer, $localCustomer, $contact);

        $this->assertSame($expectedValue, $contact->getFirstName());
    }

    /**
     * @return array
     */
    public function scalarDataProvider()
    {
        return [
            'should not override value, because of local priority'            => [
                ChannelType::LOCAL_WINS,
                self::TEST_REMOTE_DATA,
                self::TEST_LOCAL_DATA,
                self::TEST_DATA,
                self::TEST_DATA
            ],
            'should override if local priority, but field did not changed'    => [
                ChannelType::LOCAL_WINS,
                self::TEST_REMOTE_DATA,
                self::TEST_LOCAL_DATA,
                self::TEST_LOCAL_DATA,
                self::TEST_REMOTE_DATA
            ],
            'should override if remote priority even if contact data changed' => [
                ChannelType::REMOTE_WINS,
                self::TEST_REMOTE_DATA,
                self::TEST_LOCAL_DATA,
                self::TEST_DATA,
                self::TEST_REMOTE_DATA
            ],
            'should override old data if not changed'                         => [
                ChannelType::REMOTE_WINS,
                self::TEST_REMOTE_DATA,
                self::TEST_LOCAL_DATA,
                self::TEST_LOCAL_DATA,
                self::TEST_REMOTE_DATA
            ]
        ];
    }
}
