Search Bundle
========================

Search bundle create search index for mapping objects and allow to create advanced queries to this indexed data.

Install project
----------------------------------

**Update database structure**

MySql, Postgres and other db engines use additional indexes for fulltext search. To create this indexes use console command

    php app/console oro:search:create-index

Bundle config
----------------------------------

Main bundle config store in config.yml file in oro_search section.

oro_search parameter supports next parameter strings:

- **engine** set engine to use for indexing. Now supports only orm engine
- **entities_config** set array with mapping entities config.

Mapping config
----------------------------------

After insert, update or delete entity records, search index must be updated. Search index consist of data from entities by mapping parameters.

In entity mapping config we map entity fields to virtual search fields in search index.

Entity mapping configuration can be store in main config.yml file (in search bundle config section) or in search.yml files in config directory of the bundle.

Configuration is array that contain info about bundle name, entity name and array of fields.

Fields array contain array of field name and field type.

All text fields data wheel be store in **all_text** virtual field. Additionally, all the fields wheel be stored in fieldName virtual fields.

Example:

    Acme\DemoBundle\Entity\Product:
        fields:
            -
                name: name
                target_type: text
                target_fields: [name, all_data]
            -
                name: description
                target_type: text
                target_fields: [description, all_data]
            -
                name: manufacturer
                relation_type: many-to-one
                relation_fields:
                    -
                        name: name
                        target_type: text
                        target_fields: [manufacturer, all_data]
                    -
                        name: id
                        target_type: integer
                        target_fields: [manufacturer]
            -
                name: categories
                relation_type: many-to-many
                relation_fields:
                    -
                        name: name
                        target_type: text
                        target_fields: [all_data]
        flexible_manager: demo_product_manager

Parameters:

- **name**: name of field in entity
- **target_type**: type of virtual search field. Supported target types: text (string and text fields), integer, double, datetime
- **target_fields**: array of virtual fields for entity field from 'name' parameter.
- **relation_type**: indicate that this field is relation field to another table. Supported relation types: one-to-one, many-to-many, one-to-many, many-to-one.
- **relation_fields**: array of fields from relation record we must to index.
- **flexible_manager**. If entity has flexible attributes, they can be indexed for search by parameter flexible_manager in mapping config. Value of this parameter
is the service name for flexible entity. In search index wheel be indexed all the attributes with parameter **searchable** set to true. All text fields data wheel
be store in **all_text** virtual field. Additionally, all the fields wheel be stored in fieldName virtual fields.

Query builder
----------------------------------

To run search queries must be used query builder.

For example:

    $query = $this->getSearchManager()->select()
            ->from('Oro/Bundle/SearchBundle/Entity:Product')
            ->andWhere('all_data', '=', 'Functions', 'text')
            ->orWhere('price', '>', 85, 'decimal');

Syntax of Query builder as close to Doctrine 2.

**from()** method takes array or string of entities to search from. If argument of function was '*', then search wheel be run for all entites.

**andWhere()**, **orWhere()** functions set AND WHERE and OR WHERE functions in search request.

First argument - field name to search from. It can be set to '*' for searching by all fields.

Second argument - operators <, >, =, !=, etc.
If first argument is for text field, this parameter wheel be ignored.

Third argument - value to search

Fourth argument - field type.

**setFirstResult()** method set the first result offset

**setMaxResults()** method set max results of records in result.

As result of query, wheel wheel be returned Oro\Bundle\SearchBundle\Query\Result class with info about search query and result items.

API
---

REST and SOAP APIs allow to search by all text fields in all entities.

Parameters for APIs requests:

 - **search** - search string
 - **offset** - integer value of offset
 - **max_results** - count  of result records in response

REST API url: http://domail.com/api/rest/latest/search

SOAP function name: search

REST API work with get request only. So, search request to the search must be like example:
http://domail.com/api/rest/latest/search?max_results=100&offset=0&search=search_string

**Result**

Request return array with next data:

 - **records_count** - the total number of results (without offset and max_results) parameters
 - **count** - count of records in current request
 - **data**- array with data.

 Data consists from next values:

 - **entity_name** - class name of entity
 - **record_id** - id of record from this entity
 - **record_string** - the title of this record

Run unit tests
----------------------------------

To run tests for bundle, use command

    phpunit -c app src/Oro/Bundle/SearchBundle/
