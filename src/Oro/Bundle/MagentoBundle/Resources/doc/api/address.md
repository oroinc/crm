# Oro\Bundle\MagentoBundle\Entity\Address

## ACTIONS  

### get

Retrieve a specific Magento customer address record.

{@inheritdoc}

### get_list

Retrieve a collection of Magento customer address records.

{@inheritdoc}

### create

Create a new Magento customer address record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentoaddresses",
      "attributes":{  
         "label":"main",
         "street":"Lake",
         "city":"Gurdiff",
         "postalCode":"05246",
         "namePrefix":"Dr.",
         "firstName":"Yougin",
         "middleName":"Albert",
         "lastName":"Martin",
         "phone":"+14569453",
         "primary":true,
         "organization":"Sales Corp",
         "originId":"1245"
      },
      "relationships":{  
         "country":{  
            "data":{  
               "type":"countries",
               "id":"US"
            }
         },
         "region":{  
            "data":{  
               "type":"regions",
               "id":"US-MI"
            }
         },
         "owner":{  
            "data":{  
               "type":"magentocustomers",
               "id":"11"
            }
         }
      }
   }
}
```
{@/request}

### update

Edit a specific Magento customer address record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentoaddresses",
      "id":"1",
      "attributes":{  
         "label":"main",
         "street":"Lake",
         "city":"Gurdiff",
         "postalCode":"05246",
         "namePrefix":"Dr.",
         "firstName":"Yougin",
         "middleName":"Albert",
         "lastName":"Martin",
         "phone":"+14569453",
         "primary":true,
         "organization":"Sales Corp",
         "originId":"1245",
         "nameSuffix": null
      },
      "relationships":{  
         "country":{  
            "data":{  
               "type":"countries",
               "id":"US"
            }
         },
         "region":{  
            "data":{  
               "type":"regions",
               "id":"US-MI"
            }
         },
         "owner":{  
            "data":{  
               "type":"magentocustomers",
               "id":"11"
            }
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific Magento customer address record.

{@inheritdoc}

### delete_list

Delete a collection of Magento customer address records.

{@inheritdoc}

## FIELDS

### owner

#### create

{@inheritdoc}

**The required field.**

### country

#### create

{@inheritdoc}

**The required field.**

## SUBRESOURCES

### contactAddress

#### get_subresource

Retrieve the contact address record configured for a specific Magento customer address record.

#### get_relationship

Retrieve the ID of the contact address record configured for a specific Magento customer address record.

#### update_relationship

Replace the contact address configured for a specific Magento customer address record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "contactaddresses",
    "id": "77"
  }
}
```
{@/request}

### country

#### get_subresource

Retrieve the record of the country configured for a specific Magento customer address record.

#### get_relationship

Retrieve the ID of the country configured for a specific Magento customer address record.

#### update_relationship

Replace the country configured for a specific Magento customer address record.

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

### owner

#### get_subresource

Retrieve the record of the Magento customer who is the owner of a specific Magento customer address record.

#### get_relationship

Retrieve the ID of the Magento customer who is the owner of a specific Magento customer address record.

#### update_relationship

Replace the owner of a specific Magento customer address record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentocustomers",
    "id": "11"
  }
}
```
{@/request}

### region

#### get_subresource

Retrieve the record of the region configured for a specific Magento customer address record.

#### get_relationship

Retrieve the ID of the region configured for a specific Magento customer address record.

#### update_relationship

Replace the region configured for a specific Magento customer address record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "regions",
    "id": "US-MI"
  }
}
```
{@/request}

### types

#### get_subresource

Retrieve a collection of the address type records that are configured for a specific Magento customer address record.

#### get_relationship

Retrieve the IDs of the address types ('billing,' 'shipping') that are configured for a specific Magento customer address record.

#### add_relationship

Set the address types for a specific Magento customer address record.

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

Replace the address types for a specific Magento customer address record.

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

Remove the address types of a specific Magento customer address record.

### channel

#### get_subresource

Retrieve an integration channel via which information about the Magento customer address is received.

#### get_relationship

Retrieve the ID of an integration channel via which information about the Magento customer address is received.

#### update_relationship

Replace an integration channel via which information about the Magento customer address is received.

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
