<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer;
use OroCRM\Bundle\ContactBundle\Model\Social;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\Source;
use OroCRM\Bundle\ContactBundle\Entity\Method;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\AccountBundle\Entity\Account;

class ContactNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $socialUrlFormatter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    /**
     * @var ContactNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->socialUrlFormatter = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Formatter\SocialUrlFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMock('Oro\Bundle\ImportExportBundle\Serializer\Serializer');

        $this->normalizer = new ContactNormalizer();
        $this->normalizer->setSerializer($this->serializer);
        $this->normalizer->setSocialUrlFormatter($this->socialUrlFormatter);
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @expectedExceptionMessage Serializer must implement
     */
    public function testSetInvalidSerializer()
    {
        $this->normalizer->setSerializer($this->getMock('Symfony\Component\Serializer\SerializerInterface'));
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createContact()));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization('string', ContactNormalizer::CONTACT_TYPE));
        $this->assertTrue($this->normalizer->supportsDenormalization(array(), ContactNormalizer::CONTACT_TYPE));
    }

    /**
     * @dataProvider normalizeScalarFieldsDataProvider
     *
     * @param Contact $contact
     * @param array $expectedData
     */
    public function testNormalizeScalarFields(Contact $contact, array $expectedData)
    {
        $this->serializer->expects($this->never())->method($this->anything());
        $this->socialUrlFormatter->expects($this->never())->method($this->anything());
        $this->assertEquals(
            $expectedData,
            $this->normalizer->normalize($contact)
        );
    }

    /**
     * @dataProvider normalizeScalarFieldsDataProvider
     *
     * @param Contact $expectedContact
     * @param array $data
     */
    public function testDenormalizeScalarFields(Contact $expectedContact, array $data)
    {
        $this->socialUrlFormatter->expects($this->never())->method($this->anything());
        $this->serializer->expects($this->never())->method($this->anything());
        $this->assertEquals(
            $expectedContact,
            $this->normalizer->denormalize($data, ContactNormalizer::CONTACT_TYPE)
        );
    }

    public function normalizeScalarFieldsDataProvider()
    {
        return array(
            'not_empty' => array(
                $this->createContact()
                    ->setId(1)
                    ->setNamePrefix('name_prefix')
                    ->setFirstName('first_name')
                    ->setMiddleName('middle_name')
                    ->setLastName('last_name')
                    ->setNameSuffix('name_suffix')
                    ->setGender('male')
                    ->setDescription('description')
                    ->setJobTitle('job_title')
                    ->setFax('fax')
                    ->setSkype('skype')
                ,
                array(
                    'id' => 1,
                    'namePrefix' => 'name_prefix',
                    'firstName' => 'first_name',
                    'middleName' => 'middle_name',
                    'lastName' => 'last_name',
                    'nameSuffix' => 'name_suffix',
                    'gender' => 'male',
                    'description' => 'description',
                    'jobTitle' => 'job_title',
                    'fax' => 'fax',
                    'skype' => 'skype',
                    'birthday' => null,
                    'twitter' => null,
                    'facebook' => null,
                    'googlePlus' => null,
                    'linkedIn' => null,
                    'source' => null,
                    'method' => null,
                    'owner' => null,
                    'assignedTo' => null,
                    'emails' => array(),
                    'phones' => array(),
                    'groups' => array(),
                    'accounts' => array(),
                    'addresses' => array(),
                )
            ),
            'empty' => array(
                $this->createContact(),
                array(
                    'id' => null,
                    'namePrefix' => null,
                    'firstName' => null,
                    'middleName' => null,
                    'lastName' => null,
                    'nameSuffix' => null,
                    'gender' => null,
                    'birthday' => null,
                    'description' => null,
                    'jobTitle' => null,
                    'fax' => null,
                    'skype' => null,
                    'twitter' => null,
                    'facebook' => null,
                    'googlePlus' => null,
                    'linkedIn' => null,
                    'source' => null,
                    'method' => null,
                    'owner' => null,
                    'assignedTo' => null,
                    'emails' => array(),
                    'phones' => array(),
                    'groups' => array(),
                    'accounts' => array(),
                    'addresses' => array(),
                )
            ),
        );
    }

    public function testNormalizeSocialFields()
    {
        $contact = $this->createContact()
            ->setTwitter('twitter_account')
            ->setFacebook('facebook_account')
            ->setGooglePlus('google_plus_account')
            ->setLinkedIn('linkedin_account');

        $socialValueMap = array(
            array(Social::TWITTER, $contact->getTwitter(), 'twitter_url'),
            array(Social::FACEBOOK, $contact->getFacebook(), 'facebook_url'),
            array(Social::GOOGLE_PLUS, $contact->getGooglePlus(), 'google_plus_url'),
            array(Social::LINKED_IN, $contact->getLinkedIn(), 'linkedin_url'),
        );

        $this->serializer->expects($this->never())->method($this->anything());
        $this->socialUrlFormatter->expects($this->exactly(count($socialValueMap)))
            ->method('getSocialUrl')
            ->will($this->returnValueMap($socialValueMap));

        $normalizedData = $this->normalizer->normalize($contact);
        $this->assertInternalType('array', $normalizedData);

        $this->assertArrayHasKey('twitter', $normalizedData);
        $this->assertEquals('twitter_url', $normalizedData['twitter']);

        $this->assertArrayHasKey('facebook', $normalizedData);
        $this->assertEquals('facebook_url', $normalizedData['facebook']);

        $this->assertArrayHasKey('googlePlus', $normalizedData);
        $this->assertEquals('google_plus_url', $normalizedData['googlePlus']);

        $this->assertArrayHasKey('linkedIn', $normalizedData);
        $this->assertEquals('linkedin_url', $normalizedData['linkedIn']);
    }

    public function testDenormalizeSocialFields()
    {
        $this->socialUrlFormatter->expects($this->never())->method('getSocialUrl');

        $data = array(
            'twitter' => 'twitter_url',
            'facebook' => 'facebook_url',
            'googlePlus' => 'google_plus_url',
            'linkedIn' => 'linkedin_url',
        );

        $contact = $this->normalizer->denormalize($data, ContactNormalizer::CONTACT_TYPE);
        $this->assertInstanceOf(ContactNormalizer::CONTACT_TYPE, $contact);

        $this->assertEquals($data['twitter'], $contact->getTwitter());
        $this->assertEquals($data['facebook'], $contact->getFacebook());
        $this->assertEquals($data['googlePlus'], $contact->getGooglePlus());
        $this->assertEquals($data['linkedIn'], $contact->getLinkedIn());
    }

    /**
     * @dataProvider normalizeObjectFieldDataProvider
     */
    public function testNormalizeObjectField(Contact $contact, $fieldName, $object, $expectedValue, $context = array())
    {
        $format = null;

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($object, $format, $context)
            ->will($this->returnValue($expectedValue));

        $normalizedData = $this->normalizer->normalize($contact, $format);
        $this->assertInternalType('array', $normalizedData);

        $this->assertArrayHasKey($fieldName, $normalizedData);
        $this->assertEquals($expectedValue, $normalizedData[$fieldName]);
    }

    public function normalizeObjectFieldDataProvider()
    {
        return array(
            'birthday' => array(
                'contact' => $this->createContact()->setBirthday($birthday = new \DateTime()),
                'fieldName' => 'birthday',
                'object' => $birthday,
                'expectedValue' => '1928-06-14',
                'context' => array('type' => 'date'),
            ),
            'source' => array(
                'contact' => $this->createContact()->setSource($source = new Source('source')),
                'fieldName' => 'source',
                'object' => $source,
                'expectedValue' => 'source_value'
            ),
            'method' => array(
                'contact' => $this->createContact()->setMethod($method = new Method('method')),
                'fieldName' => 'method',
                'object' => $method,
                'expectedValue' => 'method_value'
            ),
            'owner' => array(
                'contact' => $this->createContact()->setOwner($owner = new User()),
                'fieldName' => 'owner',
                'object' => $owner,
                'expectedValue' => array('firstName' => 'John', 'lastName' => 'Doe'),
                'context' => array('mode' => 'short')
            ),
            'assignedTo' => array(
                'contact' => $this->createContact()->setAssignedTo($assignedTo = new User()),
                'fieldName' => 'assignedTo',
                'object' => $assignedTo,
                'expectedValue' => array('firstName' => 'Jill', 'lastName' => 'Smith'),
                'context' => array('mode' => 'short')
            ),
            'emails' => array(
                'contact' =>
                    $contact = $this->createContact()->resetEmails(
                        array(
                            new ContactEmail('first@example.com'),
                            new ContactEmail('second@example.com'),
                        )
                    ),
                'fieldName' => 'emails',
                'object' => $contact->getEmails(),
                'expectedValue' => array('first@example.com', 'second@example.com'),
            ),
            'phones' => array(
                'contact' =>
                    $contact = $this->createContact()->resetPhones(
                        array(
                            new ContactPhone('080011223344'),
                            new ContactPhone('080011223355'),
                        )
                    ),
                'fieldName' => 'phones',
                'object' => $contact->getPhones(),
                'expectedValue' => array('080011223344', '080011223355'),
            ),
            'groups' => array(
                'contact' =>
                    $contact = $this->createContact()
                        ->addGroup(new Group('First Group'))
                        ->addGroup(new Group('Second Group')),
                'fieldName' => 'groups',
                'object' => $contact->getGroups(),
                'expectedValue' => array('First Group', 'Second Group'),
            ),
            'accounts' => array(
                'contact' =>
                    $contact = $this->createContact()
                        ->addAccount($this->createAccount('First Account'))
                        ->addAccount($this->createAccount('Second Account')),
                'fieldName' => 'accounts',
                'object' => $contact->getAccounts(),
                'expectedValue' => array('First Account', 'Second Account'),
                'context' => array('mode' => 'short')
            ),
            'addresses' => array(
                'contact' =>
                    $contact = $this->createContact()
                        ->addAddress($this->createContactAddress()->setLabel('Billing'))
                        ->addAddress($this->createContactAddress()->setLabel('Shipping')),
                'fieldName' => 'addresses',
                'object' => $contact->getAddresses(),
                'expectedValue' => array(array('label' => 'Billing'), array('label' => 'Shipping')),
            ),
        );
    }

    /**
     * @dataProvider denormalizeObjectFieldDataProvider
     */
    public function testDenormalizeObjectField(
        $data,
        $fieldName,
        $object,
        $type,
        Contact $expectedContact,
        array $context = array()
    ) {
        $format = null;

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with($data[$fieldName], $type, $format, $context)
            ->will($this->returnValue($object));

        $this->assertEquals(
            $expectedContact,
            $this->normalizer->denormalize($data, ContactNormalizer::CONTACT_TYPE, $format)
        );
    }

    public function denormalizeObjectFieldDataProvider()
    {
        return array(
            'birthday' => array(
                'data' => array('birthday' => '1928-06-14'),
                'fieldName' => 'birthday',
                'object' => $birthday = new \DateTime('1928-06-14'),
                'type' => 'DateTime',
                'expectedContact' => $this->createContact()->setBirthday($birthday),
                'context' => array('type' => 'date'),
            ),
            'source' => array(
                'data' => array('source' => 'source_value'),
                'fieldName' => 'source',
                'object' => $source = new Source('source'),
                'type' => ContactNormalizer::SOURCE_TYPE,
                'expectedContact' => $this->createContact()->setSource($source),
            ),
            'method' => array(
                'data' => array('method' => 'source_value'),
                'fieldName' => 'method',
                'object' => $method = new Method('method'),
                'type' => ContactNormalizer::METHOD_TYPE,
                'expectedContact' => $this->createContact()->setMethod($method),
            ),
            'owner' => array(
                'data' => array('owner' => 'owner_value'),
                'fieldName' => 'owner',
                'object' => $owner = new User(),
                'type' => ContactNormalizer::USER_TYPE,
                'expectedContact' => $this->createContact()->setOwner($owner),
                'context' => array('mode' => 'short'),
            ),
            'assignedTo' => array(
                'data' => array('assignedTo' => 'assigned_to_value'),
                'fieldName' => 'assignedTo',
                'object' => $assignedTo = new User(),
                'type' => ContactNormalizer::USER_TYPE,
                'expectedContact' => $this->createContact()->setAssignedTo($assignedTo),
                'context' => array('mode' => 'short'),
            ),
            'emails' => array(
                'data' => array('emails' => array('first@example.com', 'second@example.com')),
                'fieldName' => 'emails',
                'object' =>
                    $emails = new ArrayCollection(
                        array(new ContactEmail('first@example.com'), new ContactEmail('second@example.com'))
                    ),
                'type' => ContactNormalizer::EMAILS_TYPE,
                'expectedContact' => $this->createContact()->resetEmails($emails),
            ),
            'phones' => array(
                'data' => array('phones' => array('080011223344', '080011223355')),
                'fieldName' => 'phones',
                'object' =>
                    $phones = new ArrayCollection(
                        array(new ContactPhone('080011223344'), new ContactPhone('080011223355'))
                    ),
                'type' => ContactNormalizer::PHONES_TYPE,
                'expectedContact' => $this->createContact()->resetPhones($phones),
            ),
            'groups' => array(
                'data' => array('groups' => array('First Group', 'Second Group')),
                'fieldName' => 'groups',
                'object' =>
                    $phones = new ArrayCollection(
                        array(new Group('First Group'), new Group('Second Group'))
                    ),
                'type' => ContactNormalizer::GROUPS_TYPE,
                'expectedContact' => $this->createContact()->addGroup($phones->get(0))->addGroup($phones->get(1)),
            ),
            'accounts' => array(
                'data' => array('accounts' => array('First Account', 'Second Account')),
                'fieldName' => 'accounts',
                'object' =>
                    $accounts = new ArrayCollection(
                        array($this->createAccount('First Account'),$this->createAccount('Second Account'))
                    ),
                'type' => ContactNormalizer::ACCOUNTS_TYPE,
                'expectedContact' =>
                    $this->createContact()
                        ->addAccount($accounts->get(0))
                        ->addAccount($accounts->get(1)),
                'context' => array('mode' => 'short'),
            ),
            'addresses' => array(
                'data' => array('addresses' => array(array('label' => 'Billing'), array('label' => 'Shipping'))),
                'fieldName' => 'addresses',
                'object' =>
                    $addresses = new ArrayCollection(
                        array($this->createContactAddress('Billing'), $this->createContactAddress('Shipping'))
                    ),
                'type' => ContactNormalizer::ADDRESSES_TYPE,
                'expectedContact' =>
                    $this->createContact()->addAddress($addresses->get(0))->addAddress($addresses->get(1)),
            ),
        );
    }

    /**
     * @return Contact
     */
    protected function createContact()
    {
        $result = new Contact();
        return $result;
    }

    /**
     * @param string|null $name
     * @return Account
     */
    protected function createAccount($name = null)
    {
        $result = new Account();
        $result->setName($name);
        return $result;
    }

    /**
     * @param string|null $label
     * @return ContactAddress
     */
    protected function createContactAddress($label = null)
    {
        $result = new ContactAddress();
        $result->setLabel($result);
        return $result;
    }
}
