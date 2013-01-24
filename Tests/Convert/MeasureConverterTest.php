<?php
namespace Oro\Bundle\MeasureBundle\Tests\Convert;

use Oro\Bundle\MeasureBundle\Family\WeightFamily;

use Oro\Bundle\MeasureBundle\Family\VolumeFamily;

use Oro\Bundle\MeasureBundle\Family\TemperatureFamily;

use Oro\Bundle\MeasureBundle\Family\SpeedFamily;

use Oro\Bundle\MeasureBundle\Family\PowerFamily;

use Oro\Bundle\MeasureBundle\Family\FrequencyFamily;

use Oro\Bundle\MeasureBundle\Family\BinaryFamily;

use Oro\Bundle\MeasureBundle\Family\AreaFamily;

use Oro\Bundle\MeasureBundle\Family\LengthFamily;

use Oro\Bundle\MeasureBundle\Convert\MeasureConverter;

use Oro\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException;

use Oro\Bundle\MeasureBundle\Exception\UnknownMeasureException;

use Oro\Bundle\MeasureBundle\Exception\UnknownOperatorException;

use Symfony\Component\Yaml\Yaml;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class MeasureConverterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Converter service
     * @var MeasureConverter
     */
    protected $converter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        // get measures configuration
        $configFile = realpath(dirname(__FILE__) .'/../../Resources/config/measure.yml');
        $config = $this->initializeConfig($configFile);

        // initialize converter
        $this->converter = $this->initializeConverter($config);
    }

    /**
     * Initialize a configuration
     * @param string $filepath
     *
     * @throws \Exception
     *
     * @return multitype
     */
    protected function initializeConfig($filepath)
    {
        if (!file_exists($filepath)) {
            throw new \Exception('Config file not exists');
        }

        return Yaml::parse($filepath);
    }

    /**
     * Initialize converter
     * @param multitype $config
     *
     * @throws \Exception
     *
     * @return \Oro\Bundle\MeasureBundle\Convert\MeasureConverter
     */
    protected function initializeConverter($config)
    {
        return new MeasureConverter($config);
    }

    /**
     * Test convert method for Area family
     */
    public function testConvertArea()
    {
        $this->converter->setFamily(AreaFamily::FAMILY);

        // test square_meter to hectare conversion
        $baseValue = 200;
        $expectedValue = 0.02;

        // convert to a final value
        $convertedValue = $this->converter->convert(AreaFamily::SQUARE_METER, AreaFamily::HECTARE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(AreaFamily::HECTARE, AreaFamily::SQUARE_METER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test square_inch to square_mil conversion
        $baseValue = 10;
        $expectedValue = 10000000;

        // convert to a final value
        $convertedValue = $this->converter->convert(AreaFamily::SQUARE_INCH, AreaFamily::SQUARE_MIL, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(AreaFamily::SQUARE_MIL, AreaFamily::SQUARE_INCH, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test square_furlong to square_mile conversion
        $baseValue = 100;
        $expectedValue = 1.5625062462063;

        // convert to a final value
        $convertedValue = $this->converter->convert(AreaFamily::SQUARE_FURLONG, AreaFamily::SQUARE_MILE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(AreaFamily::SQUARE_MILE, AreaFamily::SQUARE_FURLONG, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Binary family
     */
    public function testConvertBinary()
    {
        $this->converter->setFamily(BinaryFamily::FAMILY);

        // test byte to kilobyte conversion
        $baseValue = 1024;
        $expectedValue = 1;

        // convert to a final value
        $convertedValue = $this->converter->convert(BinaryFamily::BYTE, BinaryFamily::KILOBYTE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(BinaryFamily::KILOBYTE, BinaryFamily::BYTE, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test gigabyte to bit conversion
        $baseValue = 2;
        $expectedValue = 17179869184;

        // convert to a final value
        $convertedValue = $this->converter->convert(BinaryFamily::GIGABYTE, BinaryFamily::BIT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(BinaryFamily::BIT, BinaryFamily::GIGABYTE, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Frequency family
     */
    public function testConvertFrequency()
    {
        $this->converter->setFamily(FrequencyFamily::FAMILY);

        // test hertz to kilohertz conversion
        $baseValue = 10000;
        $expectedValue = 10;

        // convert to a final value
        $convertedValue = $this->converter->convert(FrequencyFamily::HERTZ, FrequencyFamily::KILOHERTZ, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(FrequencyFamily::KILOHERTZ, FrequencyFamily::HERTZ, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test terahertz to megahertz conversion
        $baseValue = 2;
        $expectedValue = 2000000;

        // convert to a final value
        $convertedValue = $this->converter->convert(FrequencyFamily::TERAHERTZ, FrequencyFamily::MEGAHERTZ, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(FrequencyFamily::MEGAHERTZ, FrequencyFamily::TERAHERTZ, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Length family
     */
    public function testConvertLength()
    {
        $this->converter->setFamily(LengthFamily::FAMILY);

        // test millimeter to centimeter conversion
        $baseValue = 50;
        $expectedValue = 5;

        // convert to a final value
        $convertedValue = $this->converter->convert(LengthFamily::MILLIMETER, LengthFamily::CENTIMETER, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(LengthFamily::CENTIMETER, LengthFamily::MILLIMETER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test inch to mile conversion
        $baseValue = 15000;
        $expectedValue = 0.23674242424242;

        // convert to a final value
        $convertedValue = $this->converter->convert(LengthFamily::INCH, LengthFamily::MILE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(LengthFamily::MILE, LengthFamily::INCH, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test millimeter to centimeter conversion
        $baseValue = 10;
        $expectedValue = 10936.132983377;

        // convert to a final value
        $convertedValue = $this->converter->convert(LengthFamily::KILOMETER, LengthFamily::YARD, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(LengthFamily::YARD, LengthFamily::KILOMETER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Power family
     */
    public function testConvertPower()
    {
        $this->converter->setFamily(PowerFamily::FAMILY);

        // test hertz to kilohertz conversion
        $baseValue = 10000;
        $expectedValue = 10;

        // convert to a final value
        $convertedValue = $this->converter->convert(PowerFamily::WATT, PowerFamily::KILOWATT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(PowerFamily::KILOWATT, PowerFamily::WATT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test terawatt to megawatt conversion
        $baseValue = 2;
        $expectedValue = 2000000;

        // convert to a final value
        $convertedValue = $this->converter->convert(PowerFamily::TERAWATT, PowerFamily::MEGAWATT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(PowerFamily::MEGAWATT, PowerFamily::TERAWATT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test gigawatt to terawatt conversion
        $baseValue = 5;
        $expectedValue = 0.005;

        // convert to a final value
        $convertedValue = $this->converter->convert(PowerFamily::GIGAWATT, PowerFamily::TERAWATT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(PowerFamily::TERAWATT, PowerFamily::GIGAWATT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Temperature family
     */
    public function testConvertTemperature()
    {
        $this->converter->setFamily(TemperatureFamily::FAMILY);

        // test celcius to fahrenheit conversion
        $baseValue = 20;
        $expectedValue = 68;

        // convert to a final value
        $convertedValue = $this->converter->convert(TemperatureFamily::CELCIUS, TemperatureFamily::FAHRENHEIT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(TemperatureFamily::FAHRENHEIT, TemperatureFamily::CELCIUS, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test reaumur to rankine conversion
        $baseValue = 6;
        $expectedValue = 505.17;

        // convert to a final value
        $convertedValue = $this->converter->convert(TemperatureFamily::REAUMUR, TemperatureFamily::RANKINE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(TemperatureFamily::RANKINE, TemperatureFamily::REAUMUR, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test fahrenheit to reaumur conversion
        $baseValue = 26720.33;
        $expectedValue = 11861.48;

        // convert to a final value
        $convertedValue = $this->converter->convert(TemperatureFamily::FAHRENHEIT, TemperatureFamily::REAUMUR, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(TemperatureFamily::REAUMUR, TemperatureFamily::FAHRENHEIT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Volume family
     */
    public function testConvertVolume()
    {
        $this->converter->setFamily(VolumeFamily::FAMILY);

        // test cubic meter to liter conversion
        $baseValue = 5;
        $expectedValue = 5000;

        // convert to a final value
        $convertedValue = $this->converter->convert(VolumeFamily::CUBIC_METER, VolumeFamily::LITER, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(VolumeFamily::LITER, VolumeFamily::CUBIC_METER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test milliliter to pint conversion
        $baseValue = 568.26125;
        $expectedValue = 1;

        // convert to a final value
        $convertedValue = $this->converter->convert(VolumeFamily::MILLILITER, VolumeFamily::PINT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(VolumeFamily::PINT, VolumeFamily::MILLILITER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test cubic inch to ounce conversion
        $baseValue = 12;
        $expectedValue = 6.9209283170784;

        // convert to a final value
        $convertedValue = $this->converter->convert(VolumeFamily::CUBIC_INCH, VolumeFamily::OUNCE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(VolumeFamily::OUNCE, VolumeFamily::CUBIC_INCH, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Weight family
     */
    public function testConvertWeight()
    {
        $this->converter->setFamily(WeightFamily::FAMILY);

        // test gram to ounce conversion
        $baseValue = 3059;
        $expectedValue = 107.90304960377;

        // convert to a final value
        $convertedValue = $this->converter->convert(WeightFamily::GRAM, WeightFamily::OUNCE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(WeightFamily::OUNCE, WeightFamily::GRAM, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test pound to kilogram conversion
        $baseValue = 2;
        $expectedValue = 0.90718474;

        // convert to a final value
        $convertedValue = $this->converter->convert(WeightFamily::POUND, WeightFamily::KILOGRAM, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(WeightFamily::KILOGRAM, WeightFamily::POUND, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test grain to marc conversion
        $baseValue = 500;
        $expectedValue = 0.13237775280899;

        // convert to a final value
        $convertedValue = $this->converter->convert(WeightFamily::GRAIN, WeightFamily::MARC, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(WeightFamily::MARC, WeightFamily::GRAIN, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test related exception
     * @expectedException Oro\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException
     */
    public function testUnknownFamilyMeasureException()
    {
        $this->converter->setFamily('test-family');
    }

    /**
     * Test related exception in base to standard method
     * @expectedException Oro\Bundle\MeasureBundle\Exception\UnknownMeasureException
     */
    public function testUnknownMeasureExceptionBaseToStandard()
    {
        $this->converter->setFamily(WeightFamily::FAMILY);
        $this->converter->convert('test-unit', WeightFamily::GRAM, 50);
    }

    /**
     * Test related exception in standard to final method
     * @expectedException Oro\Bundle\MeasureBundle\Exception\UnknownMeasureException
     */
    public function testUnknownMeasureExceptionStandardToFinal()
    {
        $this->converter->setFamily(WeightFamily::FAMILY);
        $this->converter->convert(WeightFamily::GRAM, 'test-unit', 50);
    }

    /**
     * Test related exception in base to standard method
     * @expectedException Oro\Bundle\MeasureBundle\Exception\UnknownOperatorException
     */
    public function testUnknownOperatorExceptionBaseToStandard()
    {
        $configFile = dirname(__FILE__) .'/../Files/measure-test.yml';
        $config = $this->initializeConfig($configFile);

        $converter = $this->initializeConverter($config);
        $converter->setFamily(LengthFamily::FAMILY);
        $converter->convert(LengthFamily::METER, LengthFamily::CENTIMETER, 100);
    }

    /**
     * Test related exception in standard to final method
     * @expectedException Oro\Bundle\MeasureBundle\Exception\UnknownOperatorException
     */
    public function testUnknownOperatorExceptionStandardToFinal()
    {
        $configFile = dirname(__FILE__) .'/../Files/measure-test.yml';
        $config = $this->initializeConfig($configFile);

        $converter = $this->initializeConverter($config);
        $converter->setFamily(LengthFamily::FAMILY);
        $converter->convert(LengthFamily::CENTIMETER, LengthFamily::METER, 100);
    }

}
