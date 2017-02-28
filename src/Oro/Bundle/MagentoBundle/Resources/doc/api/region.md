# Oro\Bundle\MagentoBundle\Entity\Region

## ACTIONS  

### get

Retrieve a specific Magento region record.

The regions specified for Magento orders by Magento customers.

### get_list

Retrieve a collection of records represented by Magento regions.

The regions specified for Magento orders by Magento customers.

### create

Create a new Magento region record.
The created record is returned in the response.

The regions specified for Magento orders by Magento customers.

{@request:json_api}
Example:

`</api/magentoregions>`

```JSON
{
  "data": {
    "type": "magentoregions",
    "attributes": {
      "combinedCode": "US-AL",
      "code": "AL",
      "countryCode": "US",
      "regionId": 1,
      "name": "Alabama"
    }
  }
}
```
{@/request}

### update

Edit a specific Magento region record.

The regions specified for Magento orders by Magento customers.

{@request:json_api}
Example:

`</api/magentoregions/1>`

```JSON
{
  "data": {
    "type": "magentoregions",
    "id": "1",
    "attributes": {
      "combinedCode": "US-AL",
      "code": "AL",
      "countryCode": "US",
      "regionId": 1,
      "name": "Alabama"
    }
  }
}
```
{@/request}

### delete

Delete a specific Magento region record.

The regions specified for Magento orders by Magento customers.

### delete_list

Delete a collection of records represented by Magento regions.
The list of records that will be deleted, could be limited by filters.

The regions specified for Magento orders by Magento customers.