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
- 

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

To define a new family, it's just necessary to define it and their units in a new config file. For example, in our demo bundle, we add the below code :

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

Create a new family
===================



Extend converter
================



Exceptions thrown
=================


