Translatable values
===================

A value can be translated if related attribute is defined as translatable.

By default, attribute is defined as not translatable, you have to setup as following :

```php
$pm = $this->container->get('product_manager');
$attributeCode = 'name';
$attribute = $pm->createAttribute(new TextType());
$attribute->setCode($attributeCode);
$attribute->setTranslatable(true);
```

You can choose value locale as following and use any locale code you want (fr, fr_FR, other, no checks, depends on application, list of locales is available in Locale Component) :

```php
$value = $pm->createFlexibleValue();
$value->setAttribute($attribute);
$value->setData('my data');
// force locale to use
$value->setLocale('fr_FR');
```

If you don't choose locale of value, it's created with locale code (high to low priority) :
- of flexible entity manager
- of flexible entity config (see default_locale)

Base flexible entity repository is designed to deal with translated values in queries, it knows the asked locale and gets relevant value if attribute is translatable.

Base flexible entity is designed to gets relevant values too, it knows the asked locale (injected with TranslatableListener).

Scopable value
==============

A value can also be scoped if related attribute is defined as scopable.

By default, attribute is defined as not scopable, you have to setup as following :

```php
$pm = $this->container->get('product_manager');
$attributeCode = 'description';
$attribute = $pm->createAttribute(new TextType());
$attribute->setCode($attributeCode);
$attribute->setTranslatable(true);
$attribute->setScopable(true);
```

Then you can use any scope code you want for value (no checks, depends on application).

```php
$pm = $this->container->get('product_manager');
$value = $pm->createFlexibleValue();
$value->setScope('my_scope_code');
$value->setAttribute($attDescription);
$value->setData('my scoped and translated value');
```

If you want associate a default scope to any created value, define it in config file with "default_scope" param.

Base flexible entity repository is designed to deal with scoped values in queries, it knows the asked scope and gets relevant value if attribute is scopable.

Base flexible entity is designed to gets relevant values too, it knows the asked scopable (injected with ScopableListener).

What's the backend type ?
=========================

To allow to type a value, you can use these doctrine mapped fields to store the data : value_string, value_integer, value_decimal, value_text, etc

The used field is defined by the backendType property of the related attribute.

The precedent do the job for basic data but there is some more complex cases and theirs related backend types :
- options : used in case of attribute of type simple or multi list
- media : used to store some metas on media related to this value
- metric : used to deal with values which have an associate measure unit (cm, km)
- price : used to deal with values which have an associate currency

You can create your own, basically, by defining the backend type to use in attribute itself and add the relevant property or relation into your value class.

Value and currency
==================

A value can be related to a currency.

You can use any currency code you want (no checks, depends on application, list of currencies is available in Locale Component).

```php
$pm = $this->container->get('product_manager');
$value = $pm->createFlexibleValue();
$value->setAttribute($attPrice);
$value->setData(100);
$value->setCurrency('EURO');
```

Value and measure unit
======================

A value can be related to a measure unit.

You can use any unit code you want (no checks, depends on application).

```php
$pm = $this->container->get('product_manager');
$value = $pm->createFlexibleValue();
$value->setAttribute($attPrice);
$value->setData(100);
$value->setUnit('cm');
```
