# Oro\Bundle\MagentoBundle\Entity\Customer

## ACTIONS  

### get

Retrieve a specific Magento customer record.

{@inheritdoc}

### get_list

Retrieve a collection of Magento customer records.

{@inheritdoc}

### create

Create a new Magento customer record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentocustomers",
      "attributes":{  
         "firstName":"Jerry",
         "lastName":"Coleman",
         "birthday":"1969-11-07",
         "email":"JerryAColeman@armyspy.com",
         "createdAt":"2017-02-15T15:42:19Z",
         "updatedAt":"2017-02-15T15:42:19Z",
         "confirmed":true,
         "guest":false,
         "isActive":false,
         "vat":"14605165",
         "lifetime":"0.0000",
         "currency":"USD",
         "originId":1
      },
      "relationships":{  
         "website":{  
            "data":{  
               "type":"magentowebsites",
               "id":"1"
            }
         },
         "store":{  
            "data":{  
               "type":"magentostores",
               "id":"1"
            }
         },
         "contact":{  
            "data":{  
               "type":"contacts",
               "id":"1"
            }
         },
         "account":{  
            "data":{  
               "type":"accounts",
               "id":"1"
            }
         },
         "owner":{  
            "data":{  
               "type":"users",
               "id":"1"
            }
         },
         "organization":{  
            "data":{  
               "type":"organizations",
               "id":"1"
            }
         },
         "dataChannel":{  
            "data":{  
               "type":"channels",
               "id":"2"
            }
         },
          "channel":{  
             "data":{  
                "type":"integrationchannels",
                "id":"2"
             }
          }
      }
   }
}
```
{@/request}

### update

Edit a specific Magento customer record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentocustomers",
      "id":"245",
      "attributes":{  
         "firstName":"Jerry",
         "lastName":"Coleman",
         "birthday":"1969-11-07",
         "email":"JerryAColeman@armyspy.com",
         "createdAt":"2017-02-15T15:42:19Z",
         "updatedAt":"2017-02-15T15:42:19Z",
         "confirmed":true,
         "guest":false,
         "isActive":false,
         "vat":"14605165",
         "lifetime":"0.0000",
         "currency":"USD",
         "originId":1
      },
      "relationships":{  
         "website":{  
            "data":{  
               "type":"magentowebsites",
               "id":"1"
            }
         },
         "store":{  
            "data":{  
               "type":"magentostores",
               "id":"1"
            }
         },
                "account":{  
            "data":{  
               "type":"accounts",
               "id":"1"
            }
         },
         "owner":{  
            "data":{  
               "type":"users",
               "id":"1"
            }
         },
         "organization":{  
            "data":{  
               "type":"organizations",
               "id":"1"
            }
         },
         "dataChannel":{  
            "data":{  
               "type":"channels",
               "id":"2"
            }
         },
         "channel":{  
            "data":{  
               "type":"integrationchannels",
               "id":"2"
            }
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific Magento customer record

{@inheritdoc}

### delete_list

Delete a collection of Magento customer records.

{@inheritdoc}

## FIELDS

### dataChannel

#### create

{@inheritdoc}

**The required field.**

### lastName

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### firstName

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### email

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### group

#### create

{@inheritdoc}

**The required field.**

### Store

#### create

{@inheritdoc}

**The required field.**

## SUBRESOURCES

### addresses

#### get_subresource

Retrieve the address records assigned to a specific Magento customer record.

#### get_relationship

Retrieve the IDs of the address records that are assigned to a Magento customer record.

#### add_relationship

Set address records for a specific Magento customer record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "magentoaddresses",
      "id": "1"
    },
    {
      "type": "magentoaddresses",
      "id": "2"
    }
  ]
}
```
{@/request}

#### update_relationship

Replace address records assigned to a specific Magento customer record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "magentoaddresses",
      "id": "1"
    },
    {
      "type": "magentoaddresses",
      "id": "2"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove address records from a specific Magento customer record.

### account

#### get_subresource

Retrieve the account records a specific Magento customer is assigned to.

#### get_relationship

Retrieve the IDs of the account records which a specific Magento customer record is assigned to.

#### update_relationship

Replace accounts assigned to a specific Magento customer record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "accounts",
    "id": "1"
  }
}
```
{@/request}

### carts

#### get_subresource

Retrieve cart records assigned to a specific Magento customer record.

#### get_relationship

Retrieve the IDs of the cart records assigned to a specific Magento customer record.

### contact

#### get_subresource

Retrieve a contact record assigned to a specific Magento customer record.

#### get_relationship

Retrieve contact IDs assigned to a specific Magento customer record.

#### update_relationship

Replace a contact record assigned to a specific Magento customer record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "contacts",
    "id": "1"
  }
}
```
{@/request}

### dataChannel

#### get_subresource

Retrieve channel record to which a specific Magento customer is assigned.

#### get_relationship

Retrieve the ID of a channel via which information about a specific Magento customer record is received.

#### update_relationship

Replace the channel for a specific Magento customer record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "channels",
    "id": "2"
  }
}
```
{@/request}

### orders

#### get_subresource

Retrieve order records the belong to a specific Magento customer record.

#### get_relationship

Retrieve the IDs of the orders created by a specific Magento customer.

### organization

#### get_subresource

Retrieve the record of the organization a specific Magento customer record belongs to.

#### get_relationship

Retrieve the ID of an organization record that a Magento customer record belongs to.

#### update_relationship

Replace the organization a specific Magento customer record belongs to.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "organizations",
    "id": "1"
  }
}
```
{@/request}

### owner

#### get_subresource

Retrieve the record of the user who is the owner of a specific Magento customer record.

#### get_relationship

Retrieve the ID of a user who is the owner of a specific Magento customer record.

#### update_relationship

Replace the owner of a specific Magento customer record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "1"
  }
}
```
{@/request}

### store

#### get_subresource

Retrieve the store record assigned to a specific Magento customer record.

#### get_relationship

Retrieve the ID of the store from which a specific Magento customer records have been received.

#### update_relationship

Replace the store from which a specific Magento customer records have been received.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentostores",
    "id": "1"
  }
}
```
{@/request}

### website

#### get_subresource

Retrieve the  Magento website record to which a specific Magento customer record is assigned.

#### get_relationship

Retrieve the ID of the Magento website to which a specific Magento customer record is assigned.

#### update_relationship

Replace the Magento website to which a specific Magento customer record is assigned.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentowebsites",
    "id": "1"
  }
}
```
{@/request}

### channel

#### get_subresource

Retrieve an integration channel via which information about the Magento customer is received.

#### get_relationship

Retrieve the ID of an integration channel via which information about the Magento customer is received.

#### update_relationship

Replace an integration channel via which information about the Magento customer is received.

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
