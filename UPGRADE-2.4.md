UPGRADE FROM 2.3 to 2.4
========================

Table of Contents
-----------------

- [MagentoBundle](#magentobundle)

MagentoBundle
-------------

The `SoapTransport` (Magento 1 default transport) and `RestTransport` (Magento 2)  classes changed format of the data 
returned by `getWebsites` method. The old response was the following:
```
[
    'id' => 'id', // Magento original webdsite id
    'code' => 'code',
    'name' => 'name',
    'default_group_id' => 'default group id'
]
```

The new response is the following:

```
[
    'website_id' => 'id', // Magento original webdsite id
    'code' => 'code',
    'name' => 'name',
    'default_group_id' => 'default group id'
]
```

As the result of these changes, the `Oro\Bundle\MagentoBundle\ImportExport\Converter\Rest\WebsiteDataConverter`class was removed. The `Oro\Bundle\MagentoBundle\ImportExport\Converter\WebsiteDataConverter` class should be used instead. 
In addition, the `@oro_magento.importexport.data_converter.rest.website`service was replaced with `@oro_magento.importexport.data_converter.website`.
