# Oro\Bundle\MagentoBundle\Entity\OrderAddress

## ACTIONS  

### get

Retrieve a specific Magento order address record.

{@inheritdoc}

### get_list

Retrieve a collection of Magento order addresses.

{@inheritdoc}

### create

Create a new Magento order address record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentoorderaddresses",
      "attributes":{  
         "street":"First street",
         "city":"City",
         "postalCode":"123456",
         "firstName":"John",
         "lastName":"Doe"
      },
      "relationships":{  
         "owner":{  
            "data":{  
               "type":"magentoorders",
               "id":"1"
            }
         },
         "country":{  
            "data":{  
               "type":"countries",
               "id":"US"
            }
         },
         "region":{  
            "data":{  
               "type":"regions",
               "id":"US-AK"
            }
         }
      }
   }
}
```
{@/request}

### update

Edit a specific Magento order address record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentoorderaddresses",
      "id":"1",
      "attributes":{  
         "street":"First street",
         "city":"City",
         "postalCode":"123456",
         "firstName":"John",
         "lastName":"Doe"
      },
      "relationships":{  
         "owner":{  
            "data":{  
               "type":"magentoorders",
               "id":"1"
            }
         },
         "country":{  
            "data":{  
               "type":"countries",
               "id":"US"
            }
         },
         "region":{  
            "data":{  
               "type":"regions",
               "id":"US-AK"
            }
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific Magento order address record.

{@inheritdoc}

### delete_list

Delete a collection of Magento order address records.

{@inheritdoc}

## SUBRESOURCES

### types

#### get_subresource

Retrieve a collection of the address type records that are configured for a specific Magento order address record.

#### get_relationship

Retrieve the IDs of the address types ('billing,' 'shipping') that are configured for a specificMagento order address record.

#### add_relationship

Set the address types for a specific Magento order address record.

{@request:json_api}
Example:

```JSON
{  
   "data":[  
      {  
         "type":"addresstypes",
         "id":"billing"
      },
      {  
         "type":"addresstypes",
         "id":"shipping"
      }
   ]
}
```
{@/request}

#### update_relationship

Replace the address types for a specific Magento order address record.

{@request:json_api}
Example:

```JSON
{  
   "data":[  
      {  
         "type":"addresstypes",
         "id":"billing"
      },
      {  
         "type":"addresstypes",
         "id":"shipping"
      }
   ]
}
```
{@/request}

#### delete_relationship

Remove the address types of a specific Magento order address record.

### region

#### get_subresource

Retrieve the record of the region configured for a specific Magento order address record.

#### get_relationship

Retrieve the ID of the region that is configured for a specific Magento order address record.

#### update_relationship

Replace the region that is configured for a specific magento order address record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "regions",
    "id": "US-AK"
  }
}
```
{@/request}

### owner

#### get_subresource

Retrieve the record of the Magento order that is the owner of a specific Magento order address record.

#### get_relationship

Retrieve the ID of the Magento order that is the owner of a specific Magento order address record.

#### update_relationship

Replace the owner of a specific Magento order address record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentoorders",
    "id": "1"
  }
}
```
{@/request}

### country

#### get_subresource

Retrieve the country record configured for a specific Magento order address record.

#### get_relationship

Retrieve the ID of the country configured for a specific Magento order address record.

#### update_relationship

Replace the country configured for a specific Magento order address record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "countries",
    "id": "US"
  }
}
```
{@/request}

### channel

#### get_subresource

Retrieve an integration channel via which information about the Magento order address is received.

#### get_relationship

Retrieve the ID of an integration channel via which information about the Magento order address is received.

#### update_relationship

Replace an integration channel via which information about the Magento order address is received.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "integrationchannels",
    "id": "1"
  }
}
```
{@/request}
