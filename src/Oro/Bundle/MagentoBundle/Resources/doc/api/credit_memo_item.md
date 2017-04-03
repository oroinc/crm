# Oro\Bundle\MagentoBundle\Entity\CreditMemoItem

## ACTIONS  

### get

Retrieve a specific Magento credit memo item record.

{@inheritdoc}

### get_list

Retrieve a collection of records represented by Magento credit memo items.

{@inheritdoc}

### create

Create a new Magento credit memo item record.
The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

`</api/magentocreditmemoitems>`

```JSON
{  
   "data":{  
      "type":"magentocreditmemoitems",      
      "attributes":{  
         "originId":12,
         "orderItemId":1,
         "taxAmount":"0.0000",
         "discountAmount":"0.0000",
         "name":"Chair",
         "sku":"sku-chair",
         "description":"chair description",
         "qty":1,
         "price":"118.8400",
         "rowTotal":"128.7988"
      },
      "relationships":{  
         "parent":{  
            "data":{  
               "type":"magentocreditmemos",
               "id":"21"
            }
         },
         "owner":{  
            "data":{  
               "type":"organizations",
               "id":"1"
            }
         },
      }
   }
}
```
{@/request}

### update

Edit a specific Magento credit memo item record.

{@inheritdoc}

{@request:json_api}
Example:

`</api/magentocreditmemoitems/1>`

```JSON
{  
   "data":{  
      "type":"magentocreditmemoitems",
      "id":"1",
      "attributes":{
         "originId":12,
         "orderItemId":1,
         "taxAmount":"0.0000",
         "discountAmount":"0.0000",
         "name":"Chair",
         "sku":"sku-chair",
         "description":"chair description",
         "qty":1,
         "price":"118.8400",
         "rowTotal":"128.7988"
      },
      "relationships":{  
         "parent":{  
            "data":{  
               "type":"magentocreditmemos",
               "id":"21"
            }
         },
         "owner":{  
            "data":{  
               "type":"organizations",
               "id":"1"
            }
         },
      }
   }
}
```
{@/request}

### delete

Delete a specific Magento credit memo item record.

{@inheritdoc}

### delete_list

Delete a collection of records represented by Magento credit memo items.
The list of records that will be deleted, could be limited by filters.

{@inheritdoc}

## FIELDS

### id

#### update

{@inheritdoc}

**The required field**

## SUBRESOURCES

### parent

#### get_subresource

Retrieve the record of a Magento credit memo to which a specific Magento credit memo item belongs.

#### get_relationship

Retrieve the ID of a Magento credit memo record to which a specific Magento credit memo item belongs.

#### update_relationship

Replace the Magento credit memo to which a specific Magento credit memo item belongs.

{@request:json_api}
Example:

`</api/magentocreditmemoitems/1/relationships/parent>`

```JSON
{
  "data": {
    "type": "magentocreditmemos",
    "id": "21"
  }
}
```
{@/request}

### owner

#### get_subresource

Retrieve the record of an organization to which a specific Magento credit memo item belongs.

#### get_relationship

Retrieve the ID of the organization record to which a specific Magento credit memo item belongs.

#### update_relationship

Replace the organization to which a specific Magento credit memo item belongs.

{@request:json_api}
Example:

`</api/magentocreditmemoitems/1/relationships/owner>`

```JSON
{
  "data": {
    "type": "organizations",
    "id": "1"
  }
}
```
{@/request}
