<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use OroCRM\Bundle\AccountBundle\ImportExport\Serializer\Normalizer\AccountNormalizer;
use OroCRM\Bundle\AccountBundle\Entity\Account;

class AccountNormalizerTest extends \PHPUnit_Framework_TestCase
{
    const ACCOUNT_TYPE = 'OroCRM\Bundle\AccountBundle\Entity\Account';

    /**
     * @var AccountNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new AccountNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createAccount()));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization(null, self::ACCOUNT_TYPE));
        $this->assertFalse($this->normalizer->supportsDenormalization(10, self::ACCOUNT_TYPE));
        $this->assertTrue($this->normalizer->supportsDenormalization('string', self::ACCOUNT_TYPE));
        $this->assertTrue($this->normalizer->supportsDenormalization(array(), self::ACCOUNT_TYPE));
    }

    /**
     * @dataProvider normalizeDataProvider
     */
    public function testNormalize(Account $object, $expectedData, array $context)
    {
        $this->assertEquals(
            $expectedData,
            $this->normalizer->normalize($object, null, $context)
        );
    }

    /**
     * @dataProvider normalizeDataProvider
     */
    public function testDenormalizeScalarFields(Account $expectedObject, $data, array $context)
    {
        $actualObject = $this->normalizer->denormalize($data, self::ACCOUNT_TYPE, null, $context);
        $this->assertEquals($expectedObject, $actualObject);
    }

    public function normalizeDataProvider()
    {
        return array(
            'not_empty' => array(
                $this->createAccount('account_name'),
                'account_name',
                array('mode' => AccountNormalizer::SHORT_MODE)
            ),
            'empty' => array(
                $this->createAccount(),
                '',
                array('mode' => AccountNormalizer::SHORT_MODE)
            ),
        );
    }

    /**
     * @expectedException Symfony\Component\Intl\Exception\NotImplementedException
     * @expectedExceptionMessage Normalization with mode "full" is not supported.
     * Please install the "intl" extension for full localization capabilities.
     */
    public function testNormalizeFullMode()
    {
        $object = $this->createAccount();
        $this->normalizer->normalize($object, null);
    }

    /**
     *
     */
    public function testDenormalizeFullMode()
    {
        $data = array();
        $result = $this->normalizer->denormalize($data, self::ACCOUNT_TYPE, null);

        $this->assertEmpty($result->getName());
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
}
