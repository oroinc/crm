# Oro\Bundle\MagentoBundle\Entity\Order

## ACTIONS  

### get

Retrieve a specific Magento order record.

{@inheritdoc}

### get_list

Retrieve a collection of Magento order records.

{@inheritdoc}

### create

Create a new Magento order record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentoorders",
      "attributes":{  
         "incrementId":"0",
         "originId":1,
         "isVirtual":false,
         "isGuest":false,
         "storeName":"Admin",
         "totalPaidAmount":215.661888,
         "totalInvoicedAmount":"215.6619",
         "totalRefundedAmount":"0.0000",
         "totalCanceledAmount":"0.0000",
         "currency":"USD",
         "paymentMethod":"Ccsave",
         "paymentDetails":"N/A",
         "subtotalAmount":"205.6619",
         "shippingAmount":"9.0000",
         "shippingMethod":"flatrate_flatrate",
         "totalAmount":"215.6619",
         "status":"Completed",
         "createdAt":"2017-02-15T15:42:19Z",
         "updatedAt":"2017-02-15T15:42:19Z",
         "firstName":"Donald",
         "lastName":"Schiller"
      },
      "relationships":{  
         "customer":{  
            "data":{  
               "type":"magentocustomers",
               "id":"18"
            }
         },
         "addresses":{  
            "data":[  
               {  
                  "type":"magentoorderaddresses",
                  "id":"1"
               },
               {  
                  "type":"magentoorderaddresses",
                  "id":"22"
               }
            ]
         },
         "store":{  
            "data":{  
               "type":"magentostores",
               "id":"1"
            }
         },
         "cart":{  
            "data":{  
               "type":"magentocarts",
               "id":"1"
            }
         },
         "items":{  
            "data":[  
               {  
                  "type":"magentoorderitems",
                  "id":"1"
               },
               {  
                  "type":"magentoorderitems",
                  "id":"2"
               }
            ]
         },
         "owner":{  
            "data":{  
               "type":"users",
               "id":"21"
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
         }
      }
   }
}
```
{@/request}

### update

Edit a specific Magento order record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentoorders",
      "id":"1",
      "attributes":{  
         "incrementId":"0",
         "isVirtual":false,
         "isGuest":false,
         "storeName":"Admin",
         "totalPaidAmount":215.661888,
         "totalInvoicedAmount":"215.6619",
         "totalRefundedAmount":"0.0000",
         "totalCanceledAmount":"0.0000",
         "currency":"USD",
         "paymentMethod":"Ccsave",
         "paymentDetails":"N/A",
         "subtotalAmount":"205.6619",
         "shippingAmount":"9.0000",
         "shippingMethod":"flatrate_flatrate",
         "totalAmount":"215.6619",
         "status":"Completed",
         "createdAt":"2017-02-15T15:42:19Z",
         "updatedAt":"2017-02-15T15:42:19Z",
         "firstName":"Donald",
         "lastName":"Schiller"
      },
      "relationships":{  
         "customer":{  
            "data":{  
               "type":"magentocustomers",
               "id":"18"
            }
         },
         "addresses":{  
            "data":[  
               {  
                  "type":"magentoorderaddresses",
                  "id":"1"
               },
               {  
                  "type":"magentoorderaddresses",
                  "id":"22"
               }
            ]
         },
         "store":{  
            "data":{  
               "type":"magentostores",
               "id":"1"
            }
         },
         "cart":{  
            "data":{  
               "type":"magentocarts",
               "id":"1"
            }
         },
         "items":{  
            "data":[  
               {  
                  "type":"magentoorderitems",
                  "id":"1"
               },
               {  
                  "type":"magentoorderitems",
                  "id":"2"
               }
            ]
         },
         "owner":{  
            "data":{  
               "type":"users",
               "id":"21"
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
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific Magento order record.

{@inheritdoc}

### delete_list

Delete a collection of Magento order records.

{@inheritdoc}

## FIELDS

### incrementId

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### status

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### dataChannel

#### create

{@inheritdoc}

**The required field.**

## SUBRESOURCES

### addresses

#### get_subresource

Retrieve the address records assigned to a specific Magento order record.

#### get_relationship

Retrieve the IDs of the address records that are assigned to a Magento order record.

#### add_relationship

Set address records for a specific Magento order record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "magentoorderaddresses",
      "id": "1"
    },
    {
      "type": "magentoorderaddresses",
      "id": "22"
    }
  ]
}
```
{@/request}

#### update_relationship

Replace address records assigned to a specific Magento order record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "magentoorderaddresses",
      "id": "1"
    },
    {
      "type": "magentoorderaddresses",
      "id": "22"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove address records from a specific Magento order record.

### items

#### get_subresource

Retrieve the records of items are assigned to a specific Magento order.

#### get_relationship

Retrieve the IDs of the items assigned to a specific Magento order record.

#### add_relationship

Set item records for a specific Magento order record.

{@request:json_api}
Example:

```JSON
{  
   "data":[  
      {  
         "type":"magentoorderitems",
         "id":"1"
      },
      {  
         "type":"magentoorderitems",
         "id":"2"
      }
   ]
}
```
{@/request}

#### update_relationship

Replace records of items assigned to a specific Magento order record.

{@request:json_api}
Example:

```JSON
{  
   "data":[  
      {  
         "type":"magentoorderitems",
         "id":"1"
      },
      {  
         "type":"magentoorderitems",
         "id":"2"
      }
   ]
}
```
{@/request}

#### delete_relationship

Remove records of items assigned to a specific Magento order record.

### orderNotes

#### get_subresource

Retrieve the records of notes are assigned to a specific Magento order.

#### get_relationship

Retrieve the IDs of the notes assigned to a specific Magento order record.

### cart

#### get_subresource

Retrieve a cart record assigned to a specific Magento order record.

#### get_relationship

Retrieve the ID of a cart record assigned to a specific Magento order record.

#### update_relationship

Replace a cart record assigned to a specific Magento order record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentocarts",
    "id": "1"
  }
}
```
{@/request}

### customer

#### get_subresource

Retrieve a  record  of Magento customer to whom a specific Magento order belongs.

#### get_relationship

Retrieve the ID of a customer record assigned to a specific Magento order record.

#### update_relationship

Replace the custom record assigned to a specific Magento order record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentocustomers",
    "id": "18"
  }
}
```
{@/request}

### dataChannel

#### get_subresource

Retrieve channel record to which a specific Magento order is assigned.

#### get_relationship

Retrieve the ID of a channel via which information about specific Magento order record is received.

#### update_relationship

Replace the channel for a specific Magento order record.

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

### owner

#### get_subresource

Retrieve the record of the user who is the owner of a specific Magento order record.

#### get_relationship

Retrieve the ID of a user who is the owner of a specific Magento order record.

#### update_relationship

Replace the owner of a specific Magento order record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "21"
  }
}
```
{@/request}

### organization

#### get_subresource

Retrieve the record of the organization a specific Magento order record belongs to.

#### get_relationship

Retrieve the ID of the organization that a specific Magento order belongs to.

#### update_relationship

Replace the organization that a specific Magento order belongs to.

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

### store

#### get_subresource

Retrieve the store record assigned to a specific Magento order record.

#### get_relationship

Retrieve the ID of the store from which a specific Magento order records have been received.

#### update_relationship

Replace the store from which a specific Magento order records have been received.

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

### channel

#### get_subresource

Retrieve an integration channel via which information about the Magento order is received.

#### get_relationship

Retrieve the ID of an integration channel via which information about the Magento order is received.

#### update_relationship

Replace an integration channel via which information about the Magento order is received.

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

### creditMemos

#### get_subresource

Retrieve credit memos via which information about the Magento order is received.

#### get_relationship

Retrieve the ID of credit memos via which information about the Magento order is received.

#### add_relationship

Set credit memos records for a specific Magento order record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "magentocreditmemos",
      "id": "1"
    }
  ]
}
```
{@/request}

#### update_relationship

Replace credit memos for a specific Magento order record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "magentocreditmemos",
      "id": "1"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove credit memos for a specific Magento order record.
