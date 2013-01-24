<?php
namespace Oro\Bundle\MeasureBundle\Tests\Convert;

use Oro\Bundle\MeasureBundle\Measure\WeightMeasure;

use Oro\Bundle\MeasureBundle\Measure\VolumeMeasure;

use Oro\Bundle\MeasureBundle\Measure\TemperatureMeasure;

use Oro\Bundle\MeasureBundle\Measure\SpeedMeasure;

use Oro\Bundle\MeasureBundle\Measure\PowerMeasure;

use Oro\Bundle\MeasureBundle\Measure\FrequencyMeasure;

use Oro\Bundle\MeasureBundle\Measure\BinaryMeasure;

use Oro\Bundle\MeasureBundle\Measure\AreaMeasure;

use Oro\Bundle\MeasureBundle\Measure\LengthMeasure;

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
        $this->converter->setFamily(AreaMeasure::FAMILY);

        // test square_meter to hectare conversion
        $baseValue = 200;
        $expectedValue = 0.02;

        // convert to a final value
        $convertedValue = $this->converter->convert(AreaMeasure::SQUARE_METER, AreaMeasure::HECTARE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(AreaMeasure::HECTARE, AreaMeasure::SQUARE_METER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test square_inch to square_mil conversion
        $baseValue = 10;
        $expectedValue = 10000000;

        // convert to a final value
        $convertedValue = $this->converter->convert(AreaMeasure::SQUARE_INCH, AreaMeasure::SQUARE_MIL, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(AreaMeasure::SQUARE_MIL, AreaMeasure::SQUARE_INCH, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test square_furlong to square_mile conversion
        $baseValue = 100;
        $expectedValue = 1.5625062462063;

        // convert to a final value
        $convertedValue = $this->converter->convert(AreaMeasure::SQUARE_FURLONG, AreaMeasure::SQUARE_MILE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(AreaMeasure::SQUARE_MILE, AreaMeasure::SQUARE_FURLONG, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Binary family
     */
    public function testConvertBinary()
    {
        $this->converter->setFamily(BinaryMeasure::FAMILY);

        // test byte to kilobyte conversion
        $baseValue = 1024;
        $expectedValue = 1;

        // convert to a final value
        $convertedValue = $this->converter->convert(BinaryMeasure::BYTE, BinaryMeasure::KILOBYTE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(BinaryMeasure::KILOBYTE, BinaryMeasure::BYTE, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test gigabyte to bit conversion
        $baseValue = 2;
        $expectedValue = 17179869184;

        // convert to a final value
        $convertedValue = $this->converter->convert(BinaryMeasure::GIGABYTE, BinaryMeasure::BIT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(BinaryMeasure::BIT, BinaryMeasure::GIGABYTE, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Frequency family
     */
    public function testConvertFrequency()
    {
        $this->converter->setFamily(FrequencyMeasure::FAMILY);

        // test hertz to kilohertz conversion
        $baseValue = 10000;
        $expectedValue = 10;

        // convert to a final value
        $convertedValue = $this->converter->convert(FrequencyMeasure::HERTZ, FrequencyMeasure::KILOHERTZ, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(FrequencyMeasure::KILOHERTZ, FrequencyMeasure::HERTZ, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test terahertz to megahertz conversion
        $baseValue = 2;
        $expectedValue = 2000000;

        // convert to a final value
        $convertedValue = $this->converter->convert(FrequencyMeasure::TERAHERTZ, FrequencyMeasure::MEGAHERTZ, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(FrequencyMeasure::MEGAHERTZ, FrequencyMeasure::TERAHERTZ, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Length family
     */
    public function testConvertLength()
    {
        $this->converter->setFamily(LengthMeasure::FAMILY);

        // test millimeter to centimeter conversion
        $baseValue = 50;
        $expectedValue = 5;

        // convert to a final value
        $convertedValue = $this->converter->convert(LengthMeasure::MILLIMETER, LengthMeasure::CENTIMETER, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(LengthMeasure::CENTIMETER, LengthMeasure::MILLIMETER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test inch to mile conversion
        $baseValue = 15000;
        $expectedValue = 0.23674242424242;

        // convert to a final value
        $convertedValue = $this->converter->convert(LengthMeasure::INCH, LengthMeasure::MILE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(LengthMeasure::MILE, LengthMeasure::INCH, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test millimeter to centimeter conversion
        $baseValue = 10;
        $expectedValue = 10936.132983377;

        // convert to a final value
        $convertedValue = $this->converter->convert(LengthMeasure::KILOMETER, LengthMeasure::YARD, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(LengthMeasure::YARD, LengthMeasure::KILOMETER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Power family
     */
    public function testConvertPower()
    {
        $this->converter->setFamily(PowerMeasure::FAMILY);

        // test hertz to kilohertz conversion
        $baseValue = 10000;
        $expectedValue = 10;

        // convert to a final value
        $convertedValue = $this->converter->convert(PowerMeasure::WATT, PowerMeasure::KILOWATT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(PowerMeasure::KILOWATT, PowerMeasure::WATT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test terawatt to megawatt conversion
        $baseValue = 2;
        $expectedValue = 2000000;

        // convert to a final value
        $convertedValue = $this->converter->convert(PowerMeasure::TERAWATT, PowerMeasure::MEGAWATT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(PowerMeasure::MEGAWATT, PowerMeasure::TERAWATT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test gigawatt to terawatt conversion
        $baseValue = 5;
        $expectedValue = 0.005;

        // convert to a final value
        $convertedValue = $this->converter->convert(PowerMeasure::GIGAWATT, PowerMeasure::TERAWATT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(PowerMeasure::TERAWATT, PowerMeasure::GIGAWATT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Temperature family
     */
    public function testConvertTemperature()
    {
        $this->converter->setFamily(TemperatureMeasure::FAMILY);

        // test celcius to fahrenheit conversion
        $baseValue = 20;
        $expectedValue = 68;

        // convert to a final value
        $convertedValue = $this->converter->convert(TemperatureMeasure::CELCIUS, TemperatureMeasure::FAHRENHEIT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(TemperatureMeasure::FAHRENHEIT, TemperatureMeasure::CELCIUS, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test reaumur to rankine conversion
        $baseValue = 6;
        $expectedValue = 505.17;

        // convert to a final value
        $convertedValue = $this->converter->convert(TemperatureMeasure::REAUMUR, TemperatureMeasure::RANKINE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(TemperatureMeasure::RANKINE, TemperatureMeasure::REAUMUR, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test fahrenheit to reaumur conversion
        $baseValue = 26720.33;
        $expectedValue = 11861.48;

        // convert to a final value
        $convertedValue = $this->converter->convert(TemperatureMeasure::FAHRENHEIT, TemperatureMeasure::REAUMUR, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(TemperatureMeasure::REAUMUR, TemperatureMeasure::FAHRENHEIT, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Volume family
     */
    public function testConvertVolume()
    {
        $this->converter->setFamily(VolumeMeasure::FAMILY);

        // test cubic meter to liter conversion
        $baseValue = 5;
        $expectedValue = 5000;

        // convert to a final value
        $convertedValue = $this->converter->convert(VolumeMeasure::CUBIC_METER, VolumeMeasure::LITER, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(VolumeMeasure::LITER, VolumeMeasure::CUBIC_METER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test milliliter to pint conversion
        $baseValue = 568.26125;
        $expectedValue = 1;

        // convert to a final value
        $convertedValue = $this->converter->convert(VolumeMeasure::MILLILITER, VolumeMeasure::PINT, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(VolumeMeasure::PINT, VolumeMeasure::MILLILITER, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test cubic inch to ounce conversion
        $baseValue = 12;
        $expectedValue = 6.9209283170784;

        // convert to a final value
        $convertedValue = $this->converter->convert(VolumeMeasure::CUBIC_INCH, VolumeMeasure::OUNCE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(VolumeMeasure::OUNCE, VolumeMeasure::CUBIC_INCH, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Test convert method for Weight family
     */
    public function testConvertWeight()
    {
        $this->converter->setFamily(WeightMeasure::FAMILY);

        // test gram to ounce conversion
        $baseValue = 3059;
        $expectedValue = 107.90304960377;

        // convert to a final value
        $convertedValue = $this->converter->convert(WeightMeasure::GRAM, WeightMeasure::OUNCE, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(WeightMeasure::OUNCE, WeightMeasure::GRAM, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test pound to kilogram conversion
        $baseValue = 2;
        $expectedValue = 0.90718474;

        // convert to a final value
        $convertedValue = $this->converter->convert(WeightMeasure::POUND, WeightMeasure::KILOGRAM, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(WeightMeasure::KILOGRAM, WeightMeasure::POUND, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);


        // test grain to marc conversion
        $baseValue = 500;
        $expectedValue = 0.13237775280899;

        // convert to a final value
        $convertedValue = $this->converter->convert(WeightMeasure::GRAIN, WeightMeasure::MARC, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        // convert to the initial value
        $resultValue = $this->converter->convert(WeightMeasure::MARC, WeightMeasure::GRAIN, $convertedValue);
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
        $this->converter->setFamily(WeightMeasure::FAMILY);
        $this->converter->convert('test-unit', WeightMeasure::GRAM, 50);
    }

    /**
     * Test related exception in standard to final method
     * @expectedException Oro\Bundle\MeasureBundle\Exception\UnknownMeasureException
     */
    public function testUnknownMeasureExceptionStandardToFinal()
    {
        $this->converter->setFamily(WeightMeasure::FAMILY);
        $this->converter->convert(WeightMeasure::GRAM, 'test-unit', 50);
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
        $converter->setFamily(LengthMeasure::FAMILY);
        $converter->convert(LengthMeasure::METER, LengthMeasure::CENTIMETER, 100);
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
        $converter->setFamily(LengthMeasure::FAMILY);
        $converter->convert(LengthMeasure::CENTIMETER, LengthMeasure::METER, 100);
    }

}
