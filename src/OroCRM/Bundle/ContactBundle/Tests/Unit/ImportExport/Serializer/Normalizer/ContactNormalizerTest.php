<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Model\Social;

class ContactNormalizerTest extends \PHPUnit_Framework_TestCase
{
    const CONTACT_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\Contact';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $socialUrlFormatter;

    /**
     * @var ContactNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->socialUrlFormatter = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Formatter\SocialUrlFormatter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->normalizer = new ContactNormalizer($this->socialUrlFormatter);
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createContact()));
    }

    /**
     * @dataProvider normalizeScalarFieldsDataProvider
     *
     * @param Contact $contact
     * @param array $expectedData
     */
    public function testNormalizeScalarFields(Contact $contact, array $expectedData)
    {
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
        $this->assertEquals(
            $expectedContact,
            $this->normalizer->denormalize($data, self::CONTACT_TYPE)
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
                    ->setLastName('last_name')
                    ->setNameSuffix('name_suffix')
                    ->setGender('male')
                    ->setBirthday(new \DateTime('1980-01-01'))
                    ->setDescription('description')
                    ->setJobTitle('job_title')
                    ->setFax('fax')
                    ->setSkype('skype')
                ,
                array(
                    'id' => 1,
                    'namePrefix' => 'name_prefix',
                    'firstName' => 'first_name',
                    'lastName' => 'last_name',
                    'nameSuffix' => 'name_suffix',
                    'gender' => 'male',
                    'birthday' => new \DateTime('1980-01-01'),
                    'description' => 'description',
                    'jobTitle' => 'job_title',
                    'fax' => 'fax',
                    'skype' => 'skype',
                    'twitter' => null,
                    'facebook' => null,
                    'googlePlus' => null,
                    'linkedIn' => null,
                )
            ),
            'empty' => array(
                $this->createContact(),
                array(
                    'id' => null,
                    'namePrefix' => null,
                    'firstName' => null,
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

        $contact = $this->normalizer->denormalize($data, self::CONTACT_TYPE);
        $this->assertInstanceOf(self::CONTACT_TYPE, $contact);

        $this->assertEquals($data['twitter'], $contact->getTwitter());
        $this->assertEquals($data['facebook'], $contact->getFacebook());
        $this->assertEquals($data['googlePlus'], $contact->getGooglePlus());
        $this->assertEquals($data['linkedIn'], $contact->getLinkedIn());
    }

    /**
     * @return Contact
     */
    protected function createContact()
    {
        $result = new Contact();
        return $result;
    }
}
