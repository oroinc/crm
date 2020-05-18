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

The updated record is returned in the response.

The regions specified for Magento orders by Magento customers.

{@request:json_api}
Example:

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

The regions specified for Magento orders by Magento customers.

## FIELDS

### code

The part of after hyphen of an ISO 3166-2 region code.

### combinedCode

The identifier of an entity. The region (country subdivision) code according to ISO 3166-2.

### countryCode

The country code specified for a region. The part before hyphen of an ISO 3166-2 region code.

### name

The name used to refer to a region on the interface.

### regionId

The region ID assigned to a Magento region.
