<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Model;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\BooleanType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\DateType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\FileType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\ImageType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\IntegerType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\EmailType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MetricType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MoneyType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\NumberType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiCheckboxType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiSelectType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleRadioType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleSelectType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\UrlType;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class AttributeTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractAttributeType
     */
    protected $booleanType;

    /**
     * @var AbstractAttributeType
     */
    protected $dateType;

    /**
     * @var AbstractAttributeType
     */
    protected $fileType;

    /**
     * @var AbstractAttributeType
     */
    protected $imageType;

    /**
     * @var AbstractAttributeType
     */
    protected $integerType;

    /**
     * @var AbstractAttributeType
     */
    protected $emailType;

    /**
     * @var AbstractAttributeType
     */
    protected $metricType;

    /**
     * @var AbstractAttributeType
     */
    protected $moneyType;

    /**
     * @var AbstractAttributeType
     */
    protected $numberType;

    /**
     * @var AbstractAttributeType
     */
    protected $optMCType;

    /**
     * @var AbstractAttributeType
     */
    protected $optMSType;

    /**
     * @var AbstractAttributeType
     */
    protected $optSRType;

    /**
     * @var AbstractAttributeType
     */
    protected $optSSType;

    /**
     * @var AbstractAttributeType
     */
    protected $textareaType;

    /**
     * @var AbstractAttributeType
     */
    protected $textType;

    /**
     * @var AbstractAttributeType
     */
    protected $urlType;

    /**
     * Set up unit test
     */
    public function setUp()
    {
        $this->booleanType  = new BooleanType();
        $this->dateType     = new DateType();
        $this->fileType     = new FileType();
        $this->imageType    = new ImageType();
        $this->integerType  = new IntegerType();
        $this->emailType    = new EmailType();
        $this->metricType   = new MetricType();
        $this->moneyType    = new MoneyType();
        $this->numberType   = new NumberType();
        $this->optMCType    = new OptionMultiCheckboxType();
        $this->optMSType    = new OptionMultiSelectType();
        $this->optSRType    = new OptionSimpleRadioType();
        $this->optSSType    = new OptionSimpleSelectType();
        $this->textareaType = new TextAreaType();
        $this->textType     = new TextType();
        $this->urlType      = new UrlType();
    }

    /**
     * test related method
     */
    public function testGetName()
    {
        $this->assertEquals($this->booleanType->getName(), 'Boolean');
    }

    /**
     * test related method
     */
    public function testGetBackendType()
    {
        $this->assertEquals($this->booleanType->getBackendType(), AbstractAttributeType::BACKEND_TYPE_INTEGER);
    }

    /**
     * test related method
     */
    public function testGetFormType()
    {
        $this->assertEquals($this->booleanType->getFormType(), 'option');
    }
}
