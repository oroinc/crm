MeasureBundle
=============

BAP Measure Bundle : manage measure units in families and conversions from a unit to another

Allows to :
- Convert a value from a unit to another
- Add more unit to a family (group of measure units)
- Create new families

Classes and files
=================

In MeasureBundle :

- Measure/ contains a list of classes. Each class define a family and must contains a constant named FAMILY.


Install and run unit tests
==========================

To run tests :
```bash
$ php composer.phar update --dev

$ phpunit --coverage-html=cov/
```

Convert a value
===============

A service is defined to use converter. You must call it and define the family to use before convert a value.
In the example below, we convert a value in kilometers to miles.

```php
$converter = $this->container->get('oro_measure.measure_converter');
$converter->setFamily(LengthFamily::FAMILY);
$result = $converter->convert(LengthMeasure::KILOMETER, LengthMeasure::MILE, 1);
```

Add unit to an existing family
==============================

To define a new unit in an existing family, it's just necessary to define it and their units in a new config file named measure.yml in your own bundle. For example, in our demo bundle, we add the below code :

```yaml
measures_config:
    Length:
        standard: METER
        units:
            DONG:
                convert: [{'mul': 7},{'div': 300}]
                symbol: dong
```

Here, we just had "Dong" unit with his conversion rules from it to standard unit. To have equivalent to 1 dong in meters, you must multiply by 7 and divide by 300.
A symbol is required too to define unit format to display.
Optionally but recommended, a new class extending family class can be created. It allows to use converter with constants instead of strings. Contants represent config values.
Here we created "MyLengthMeasure" new class extending LengthMeasure to add "Dong" unit constant.

```php
use Oro\Bundle\MeasureBundle\Measure\LengthMeasure;

/**
 * Override LengthMeasure class to add Dong measure constant
 */
class MyLengthMeasure extends LengthMeasure
{

    /**
     * @staticvar string
     */
    const DONG = 'DONG';

}
```

Then, you can call a conversion to your new unit like this :

```php
$converter = $this->container->get('oro_measure.measure_converter');
$converter->setFamily(LengthMeasure::FAMILY);
$result = $converter->convert(LengthMeasure::KILOMETER, MyLengthMeasure::DONG, 1);
```

Create a new family
===================

To create a new family, it's like to add a unit to an existing family. It's necessary to add configuration in measure.yml file of your bundle and optionally a class defining constants to be used instead of strings.

```yaml
measures_config:
    Capacitance:
        standard: FARAD
        units:
            FARAD:
                convert: [{'mul': 1}]
                format: F
            KILOFARAD:
                convert: [{'mul': 1000}]
                format: kF
            MEGAFARAD:
                convert: [{'mul': 1000000}]
                format: MF
```

```php
use Oro\Bundle\MeasureBundle\Measure\AbstractMeasure;

/**
 * Capacitance measures constants
 */
class CapacitanceMeasure extends AbstractMeasure
{

    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'Capacitance';

    /**
     * @staticvar string
     */
    const FARAD     = 'FARAD';

    /**
     * @staticvar string
     */
    const KILOFARAD = 'KILOFARAD';

    /**
     * @staticvar string
     */
    const MEGAFARAD = 'MEGAFARAD';

}
```

Exceptions thrown
=================

Exceptions are thrown if we encountered problems during conversion.
- UnknownFamilyMeasureException if you try to use an unexistent or undefined family.
- UnknownMeasureException if you try to convert an unexistent or undefined unit for the family used.
- UnknownOperatorException if you try to use an unexistent operation (authorized add, sub, mul and div)

Divisions by zero don't throw exceptions but are ignored.

Extend converter
================


