<?php
namespace Oro\Bundle\MeasureBundle\Tests\Convert;

use Oro\Bundle\MeasureBundle\Family\WeightFamilyInterface;

use Oro\Bundle\MeasureBundle\Family\VolumeFamilyInterface;

use Oro\Bundle\MeasureBundle\Family\TemperatureFamilyInterface;

use Oro\Bundle\MeasureBundle\Family\SpeedFamilyInterface;

use Oro\Bundle\MeasureBundle\Family\PowerFamilyInterface;

use Oro\Bundle\MeasureBundle\Family\FrequencyFamilyInterface;

use Oro\Bundle\MeasureBundle\Family\BinaryFamilyInterface;

use Oro\Bundle\MeasureBundle\Family\AreaFamilyInterface;

use Oro\Bundle\MeasureBundle\Family\LengthFamilyInterface;

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
        $this->converter->setFamily(AreaFamilyInterface::FAMILY);

        // test square_meter to hectare conversion
        $baseValue = 200;
        $expectedValue = 0.02;

        // convert to a final value
        $convertedValue = $this->converter->convert(AreaFamilyInterface::SQUARE_METER, AreaFamilyInterface::HECTARE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(AreaFamilyInterface::HECTARE, AreaFamilyInterface::SQUARE_METER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test square_inch to square_mil conversion
        $baseValue = 10;
        $expectedValue = 10000000;

        // convert to a final value
        $convertedValue = $this->converter->convert(AreaFamilyInterface::SQUARE_INCH, AreaFamilyInterface::SQUARE_MIL, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(AreaFamilyInterface::SQUARE_MIL, AreaFamilyInterface::SQUARE_INCH, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test square_furlong to square_mile conversion
        $baseValue = 100;
        $expectedValue = 1.5625062462063;

        // convert to a final value
        $convertedValue = $this->converter->convert(AreaFamilyInterface::SQUARE_FURLONG, AreaFamilyInterface::SQUARE_MILE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(AreaFamilyInterface::SQUARE_MILE, AreaFamilyInterface::SQUARE_FURLONG, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Binary family
     */
    public function testConvertBinary()
    {
        $this->converter->setFamily(BinaryFamilyInterface::FAMILY);

        // test byte to kilobyte conversion
        $baseValue = 1024;
        $expectedValue = 1;

        // convert to a final value
        $convertedValue = $this->converter->convert(BinaryFamilyInterface::BYTE, BinaryFamilyInterface::KILOBYTE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(BinaryFamilyInterface::KILOBYTE, BinaryFamilyInterface::BYTE, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test gigabyte to bit conversion
        $baseValue = 2;
        $expectedValue = 17179869184;

        // convert to a final value
        $convertedValue = $this->converter->convert(BinaryFamilyInterface::GIGABYTE, BinaryFamilyInterface::BIT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(BinaryFamilyInterface::BIT, BinaryFamilyInterface::GIGABYTE, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Frequency family
     */
    public function testConvertFrequency()
    {
        $this->converter->setFamily(FrequencyFamilyInterface::FAMILY);

        // test hertz to kilohertz conversion
        $baseValue = 10000;
        $expectedValue = 10;

        // convert to a final value
        $convertedValue = $this->converter->convert(FrequencyFamilyInterface::HERTZ, FrequencyFamilyInterface::KILOHERTZ, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(FrequencyFamilyInterface::KILOHERTZ, FrequencyFamilyInterface::HERTZ, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test terahertz to megahertz conversion
        $baseValue = 2;
        $expectedValue = 2000000;

        // convert to a final value
        $convertedValue = $this->converter->convert(FrequencyFamilyInterface::TERAHERTZ, FrequencyFamilyInterface::MEGAHERTZ, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(FrequencyFamilyInterface::MEGAHERTZ, FrequencyFamilyInterface::TERAHERTZ, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Length family
     */
    public function testConvertLength()
    {
        $this->converter->setFamily(LengthFamilyInterface::FAMILY);

        // test millimeter to centimeter conversion
        $baseValue = 50;
        $expectedValue = 5;

        // convert to a final value
        $convertedValue = $this->converter->convert(LengthFamilyInterface::MILLIMETER, LengthFamilyInterface::CENTIMETER, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(LengthFamilyInterface::CENTIMETER, LengthFamilyInterface::MILLIMETER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test inch to mile conversion
        $baseValue = 15000;
        $expectedValue = 0.23674242424242;

        // convert to a final value
        $convertedValue = $this->converter->convert(LengthFamilyInterface::INCH, LengthFamilyInterface::MILE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(LengthFamilyInterface::MILE, LengthFamilyInterface::INCH, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test millimeter to centimeter conversion
        $baseValue = 10;
        $expectedValue = 10936.132983377;

        // convert to a final value
        $convertedValue = $this->converter->convert(LengthFamilyInterface::KILOMETER, LengthFamilyInterface::YARD, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(LengthFamilyInterface::YARD, LengthFamilyInterface::KILOMETER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Power family
     */
    public function testConvertPower()
    {
        $this->converter->setFamily(PowerFamilyInterface::FAMILY);

        // test hertz to kilohertz conversion
        $baseValue = 10000;
        $expectedValue = 10;

        // convert to a final value
        $convertedValue = $this->converter->convert(PowerFamilyInterface::WATT, PowerFamilyInterface::KILOWATT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(PowerFamilyInterface::KILOWATT, PowerFamilyInterface::WATT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test terawatt to megawatt conversion
        $baseValue = 2;
        $expectedValue = 2000000;

        // convert to a final value
        $convertedValue = $this->converter->convert(PowerFamilyInterface::TERAWATT, PowerFamilyInterface::MEGAWATT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(PowerFamilyInterface::MEGAWATT, PowerFamilyInterface::TERAWATT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test gigawatt to terawatt conversion
        $baseValue = 5;
        $expectedValue = 0.005;

        // convert to a final value
        $convertedValue = $this->converter->convert(PowerFamilyInterface::GIGAWATT, PowerFamilyInterface::TERAWATT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(PowerFamilyInterface::TERAWATT, PowerFamilyInterface::GIGAWATT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Temperature family
     */
    public function testConvertTemperature()
    {
        $this->converter->setFamily(TemperatureFamilyInterface::FAMILY);

        // test celcius to fahrenheit conversion
        $baseValue = 20;
        $expectedValue = 68;

        // convert to a final value
        $convertedValue = $this->converter->convert(TemperatureFamilyInterface::CELCIUS, TemperatureFamilyInterface::FAHRENHEIT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(TemperatureFamilyInterface::FAHRENHEIT, TemperatureFamilyInterface::CELCIUS, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test reaumur to rankine conversion
        $baseValue = 6;
        $expectedValue = 505.17;

        // convert to a final value
        $convertedValue = $this->converter->convert(TemperatureFamilyInterface::REAUMUR, TemperatureFamilyInterface::RANKINE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(TemperatureFamilyInterface::RANKINE, TemperatureFamilyInterface::REAUMUR, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test fahrenheit to reaumur conversion
        $baseValue = 26720.33;
        $expectedValue = 11861.48;

        // convert to a final value
        $convertedValue = $this->converter->convert(TemperatureFamilyInterface::FAHRENHEIT, TemperatureFamilyInterface::REAUMUR, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(TemperatureFamilyInterface::REAUMUR, TemperatureFamilyInterface::FAHRENHEIT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Volume family
     */
    public function testConvertVolume()
    {
        $this->converter->setFamily(VolumeFamilyInterface::FAMILY);

        // test cubic meter to liter conversion
        $baseValue = 5;
        $expectedValue = 5000;

        // convert to a final value
        $convertedValue = $this->converter->convert(VolumeFamilyInterface::CUBIC_METER, VolumeFamilyInterface::LITER, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(VolumeFamilyInterface::LITER, VolumeFamilyInterface::CUBIC_METER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test milliliter to pint conversion
        $baseValue = 568.26125;
        $expectedValue = 1;

        // convert to a final value
        $convertedValue = $this->converter->convert(VolumeFamilyInterface::MILLILITER, VolumeFamilyInterface::PINT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(VolumeFamilyInterface::PINT, VolumeFamilyInterface::MILLILITER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test cubic inch to ounce conversion
        $baseValue = 12;
        $expectedValue = 6.9209283170784;

        // convert to a final value
        $convertedValue = $this->converter->convert(VolumeFamilyInterface::CUBIC_INCH, VolumeFamilyInterface::OUNCE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(VolumeFamilyInterface::OUNCE, VolumeFamilyInterface::CUBIC_INCH, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Weight family
     */
    public function testConvertWeight()
    {
        $this->converter->setFamily(WeightFamilyInterface::FAMILY);

        // test gram to ounce conversion
        $baseValue = 3059;
        $expectedValue = 107.90304960377;

        // convert to a final value
        $convertedValue = $this->converter->convert(WeightFamilyInterface::GRAM, WeightFamilyInterface::OUNCE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(WeightFamilyInterface::OUNCE, WeightFamilyInterface::GRAM, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test pound to kilogram conversion
        $baseValue = 2;
        $expectedValue = 0.90718474;

        // convert to a final value
        $convertedValue = $this->converter->convert(WeightFamilyInterface::POUND, WeightFamilyInterface::KILOGRAM, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(WeightFamilyInterface::KILOGRAM, WeightFamilyInterface::POUND, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test grain to marc conversion
        $baseValue = 500;
        $expectedValue = 0.13237775280899;

        // convert to a final value
        $convertedValue = $this->converter->convert(WeightFamilyInterface::GRAIN, WeightFamilyInterface::MARC, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(WeightFamilyInterface::MARC, WeightFamilyInterface::GRAIN, $convertedValue);
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
        $this->converter->setFamily(WeightFamilyInterface::FAMILY);
        $this->converter->convert('test-unit', WeightFamilyInterface::GRAM, 50);
    }

    /**
     * Test related exception in standard to final method
     * @expectedException Oro\Bundle\MeasureBundle\Exception\UnknownMeasureException
     */
    public function testUnknownMeasureExceptionStandardToFinal()
    {
        $this->converter->setFamily(WeightFamilyInterface::FAMILY);
        $this->converter->convert(WeightFamilyInterface::GRAM, 'test-unit', 50);
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
        $converter->setFamily(LengthFamilyInterface::FAMILY);
        $converter->convert(LengthFamilyInterface::METER, LengthFamilyInterface::CENTIMETER, 100);
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
        $converter->setFamily(LengthFamilyInterface::FAMILY);
        $converter->convert(LengthFamilyInterface::CENTIMETER, LengthFamilyInterface::METER, 100);
    }

}
