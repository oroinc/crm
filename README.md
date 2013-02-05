DataFlowBundle
==============

Data import, export, transformation and mapping management

Main classes /  concepts
========================

This bundle provides bases classes to manipulate data :
- Extractors : to read data from csv file, xml file, excel file, dbal query, orm query, etc
- Tranformers : to convert data (row / item or value), as datetime converter, charset converter, object converter, callback converter (allow to easily define a simple transform), etc
- Loaders : to export / load data to csv, xml, excel file, database table (orm / dbal)

It provides a way to add some connectors as services and theirs related jobs :
- Connector : a service which define its own jobs to provide some useful business actions related to a system (for instance, Magento)
- Job : use source(s), use readers, writers, transformers to process a business action (as import products from a csv file, export PIM product to Magento, etc)
- SourceType : DatabaseSource, FtpSource, WSSource, etc related to a configuration (ex: host, port, login, passwd, dbname, driver) and allows to validate it
- Source : a named configuration (ex, code: magento_database) related to a type (ex: DatabaseSource) :


ConfigurationInterface


Job ImportAttributeJob :
- setDbSource(SourceDb $masource)
- isRunable() -> check que les sources sont bien configurés


Magento : 
- conf db    <- job import attribut : new ImportAttributeJob(SourceDb $masource)
- ftp        <- job import image
- webservice <- import stock

Source =

- Code : ma_database_magento
- Type : DB
- Configuration : {host, port, login, passwd, dbname, driver}

- Code : mon_ftp_magento
- Type : FTP
- Configuration : {host, port, login, passwd, mode[active|passive]} 


Source->isValid() (pour une db on teste la connexion)


TODO : source and configuration ?


Create a connector
==================

TODO

Create a job
============

Run a job
=========



TODO / Technical use cases
==========================

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
