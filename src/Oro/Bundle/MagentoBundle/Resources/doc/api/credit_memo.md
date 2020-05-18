# Oro\Bundle\MagentoBundle\Entity\CreditMemo

## ACTIONS  

### get

Retrieve a specific Magento credit memo record.

{@inheritdoc}

### get_list

Retrieve a collection of Magento credit memos records.

{@inheritdoc}

### create

Create a new Magento credit memo record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentocreditmemos",
      "attributes":{  
         "incrementId":"100000001",
         "originId":1,
         "invoiceId":1,
         "transactionId":"100043X231",
         "emailSent":true,
         "adjustment":"0.0000",
         "subtotal":"215.6600",
         "adjustmentNegative":"0.0000",
         "shippingAmount":"5.0000",
         "grandTotal":"220.6600",
         "adjustmentPositive":"0.0000",
         "customerBalTotalRefunded":"0.0000",
         "rewardPointsBalanceRefund":"0.0000",
         "status":"Completed",
         "createdAt":"2017-02-15T15:42:19Z",
         "updatedAt":"2017-02-15T15:42:19Z",
         "importedAt":"2017-02-16T15:42:19Z",
         "syncedAt":"2017-02-16T15:42:19Z",
      },
      "relationships":{  
         "order":{  
            "data":{  
               "type":"magentoorders",
               "id":"18"
            }
         },
         "store":{  
            "data":{  
               "type":"magentostores",
               "id":"1"
            }
         },
         "items":{  
            "data":[  
               {  
                  "type":"magentocreditmemoitems",
                  "id":"1"
               },
               {  
                  "type":"magentocreditmemoitems",
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
         },
         "status":{
            "data": {
               "type": "magentocreditmemostatuses",
               "id": "2"
            }
         }
      }
   }
}
```
{@/request}

### update

Edit a specific Magento credit memo record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentocreditmemos",
      "id":"1",
      "attributes":{  
         "incrementId":"100000001",
         "originId":1,
         "invoiceId":1,
         "transactionId":"100043X231",
         "emailSent":true,
         "adjustment":"0.0000",
         "subtotal":"215.6600",
         "adjustmentNegative":"0.0000",
         "shippingAmount":"5.0000",
         "grandTotal":"220.6600",
         "adjustmentPositive":"0.0000",
         "customerBalTotalRefunded":"0.0000",
         "rewardPointsBalanceRefund":"0.0000",
         "createdAt":"2017-02-15T15:42:19Z",
         "updatedAt":"2017-02-15T15:42:19Z",
         "importedAt":"2017-02-16T15:42:19Z",
         "syncedAt":"2017-02-16T15:42:19Z",
      },
      "relationships":{  
         "order":{  
            "data":{  
               "type":"magentoorders",
               "id":"18"
            }
         },
         "store":{  
            "data":{  
               "type":"magentostores",
               "id":"1"
            }
         },
         "items":{  
            "data":[  
               {  
                  "type":"magentocreditmemoitems",
                  "id":"1"
               },
               {  
                  "type":"magentocreditmemoitems",
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
         },
         "status":{
            "data": {
               "type": "magentocreditmemostatuses",
               "id": "2"
            }
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific Magento credit memo record.

{@inheritdoc}

### delete_list

Delete a collection of Magento credit memo records.

{@inheritdoc}

## FIELDS

### incrementId

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

### originId

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

### createdAt

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

### updatedAt

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

### order

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

### items

#### get_subresource

Retrieve the records of items are assigned to a specific Magento credit memo.

#### get_relationship

Retrieve the IDs of the items assigned to a specific Magento credit memo record.

#### add_relationship

Set item records for a specific Magento credit memo record.

{@request:json_api}
Example:

```JSON
{  
   "data":[  
      {  
         "type":"magentocreditmemoitems",
         "id":"1"
      },
      {  
         "type":"magentocreditmemoitems",
         "id":"2"
      }
   ]
}
```
{@/request}

#### update_relationship

Replace records of items assigned to a specific Magento credit memo record.

{@request:json_api}
Example:

```JSON
{  
   "data":[  
      {  
         "type":"magentocreditmemoitems",
         "id":"1"
      },
      {  
         "type":"magentocreditmemoitems",
         "id":"2"
      }
   ]
}
```
{@/request}

#### delete_relationship

Remove records of items assigned to a specific Magento credit memo record.

### order

#### get_subresource

Retrieve an order record assigned to a specific Magento credit memo record.

#### get_relationship

Retrieve the ID of an order record assigned to a specific Magento credit memo record.

#### update_relationship

Replace an order record assigned to a specific Magento credit memo record.

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

### dataChannel

#### get_subresource

Retrieve channel record to which a specific Magento credit memo is assigned.

#### get_relationship

Retrieve the ID of a channel via which information about specific Magento credit memo record is received.

#### update_relationship

Replace the channel for a specific Magento credit memo record.

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

Retrieve the record of the user who is the owner of a specific Magento credit memo record.

#### get_relationship

Retrieve the ID of a user who is the owner of a specific Magento credit memo record.

#### update_relationship

Replace the owner of a specific Magento credit memo record.

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

### store

#### get_subresource

Retrieve the store record assigned to a specific Magento credit memo record.

#### get_relationship

Retrieve the ID of the store from which a specific Magento credit memo records have been received.

#### update_relationship

Replace the store from which a specific Magento credit memo records have been received.

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

### status

#### get_subresource

Retrieve the status record configured for a specific Magento credit memo record.

#### get_relationship

Retrieve the ID of the status record configured for a specific Magento credit memo record.

#### update_relationship

Replace the status of a specific Magento credit memo record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentocreditmemostatuses",
    "id": "2"
  }
}
```
{@/request}

### channel

#### get_subresource

Retrieve an integration channel via which information about the Magento credit memo is received.

#### get_relationship

Retrieve the ID of an integration channel via which information about the Magento credit memo is received.

#### update_relationship

Replace an integration channel via which information about the Magento credit memo is received.

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

### organization

#### get_subresource

Retrieve the record of the organization a specific Magento credit memo record belongs to.

#### get_relationship

Retrieve the ID of an organization record that a Magento credit memo record belongs to.

#### update_relationship

Replace the organization a specific Magento credit memo record belongs to.

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


# Extend\Entity\EV_Creditmemo_Status

## ACTIONS

### get

Retrieve a specific Magento credit memo status record.

### get_list

Retrieve a collection of Magento credit memo status records.

## FIELDS

### name

The name of the credit memo status (`Pending`, `Refunded` or `Canceled`, corresponding to ids 1,2 and 3 in Magento).

### default

Determines whether a particular status is set as default for new credit memo record.

### priority

The order in which credit memo statuses are ranked. First appears the status with the higher priority.
