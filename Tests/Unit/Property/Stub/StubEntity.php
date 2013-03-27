<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property\Stub;

/**
 * Stub entity class for FieldPropertyTest::testGetValue
 */
class StubEntity
{
    /**
     * Test parameters
     */
    const PUBLIC_PROPERTY_NAME    = 'publicProperty';
    const CODE_METHOD_NAME        = 'computeParameter';
    const CODE_METHOD_RESULT      = 'code_method_result';
    const CODE_METHOD_NAME_NULL   = 'computeNullParameter';
    const GETTER_PROPERTY_NAME    = 'property_data';
    const GETTER_PROPERTY_RESULT  = 'getter_property_result';
    const CHECKER_PROPERTY_NAME   = 'additional_data';
    const CHECKER_PROPERTY_RESULT = 'checker_property_result';

    /**
     * @var mixed
     */
    public $publicProperty;

    public function __construct($publicProperty = null)
    {
        $this->publicProperty = $publicProperty;
    }

    public function computeParameter()
    {
        return self::CODE_METHOD_RESULT;
    }

    public function computeNullParameter()
    {
        return null;
    }

    public function getPropertyData()
    {
        return self::GETTER_PROPERTY_RESULT;
    }

    public function isAdditionalData()
    {
        return self::CHECKER_PROPERTY_RESULT;
    }
}
