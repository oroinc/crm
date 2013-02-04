DataFlowBundle
==============

Data import, export, transformation and mapping management

Main classes /  concepts
========================

- Source : aims to configure a source where data could be read
- Reader : read data from a source with CsvReader, ExcelReader, DbalReader, DoctrineReader (ORM / ODM)
- Writer : write data to a destination with CsvWriter, ExcelWriter, DbalWriter, DoctrineWriter (ORM / ODM)
- Transformer : some generic transformation of an object or structure to another one
- Mapper : aims to define and save a mapping between different data structure or objects
- Job : use readers, writers, transformers to process a more high level task (as import Magento products from a csv file)
- Connector : a service which define its own jobs to provide some useful business actions related to a system (for instance, Magento)

Technical use cases
===================

Use a connector
---------------

```php
$connector = $this->container->get('oro_dataflow.pim_magento_connector');
// call high level actions, instanciate relevant dataflow, as new MagentoProductImport() and call process()
$connector->importAttributes();
$connector->importProducts();
```

Define a dataflow
-----------------

```php
// define source
$source = new FileSource('/tmp/magento-attribute.csv');
$stream = $source->getStream();

// define reader
$reader = new CsvReader($stream, ';', '"');

// declare dataflow
$dataflow = new Job($reader);

$dataflow
    ->addTransformer('field_code1', new DateTimeTransformer())
    ->addTransformer('field_code2', new TrimTransformer());

$dataflow
    ->addWriter();
```
