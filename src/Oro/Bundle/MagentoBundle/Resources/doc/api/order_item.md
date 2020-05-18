# Oro\Bundle\MagentoBundle\Entity\OrderItem

## ACTIONS  

### get

Retrieve a specific Magento order item record.

{@inheritdoc}

### get_list

Retrieve a collection of records represented by Magento order items.

{@inheritdoc}

### create

Create a new Magento order item record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentoorderitems",      
      "attributes":{  
         "productType":"simple",
         "isVirtual":false,
         "originalPrice":"118.8400",
         "name":"Chair",
         "sku":"sku-chair",
         "qty":1,
         "price":"118.8400",
         "taxPercent":0.0838,
         "taxAmount":"9.9588",
         "rowTotal":"128.7988"
      },
      "relationships":{  
         "order":{  
            "data":{  
               "type":"magentoorders",
               "id":"21"
            }
         },
         "owner":{  
            "data":{  
               "type":"organizations",
               "id":"1"
            }
         }
      }
   }
}
```
{@/request}

### update

Edit a specific Magento order item record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentoorderitems",
      "id":"1",
      "attributes":{  
         "productType":"simple",
         "isVirtual":false,
         "originalPrice":"118.8400",
         "name":"Chair",
         "sku":"sku-chair",
         "qty":1,
         "price":"118.8400",
         "taxPercent":0.0838,
         "taxAmount":"9.9588",
         "rowTotal":"128.7988"
      },
      "relationships":{  
         "order":{  
            "data":{  
               "type":"magentoorders",
               "id":"21"
            }
         },
         "owner":{  
            "data":{  
               "type":"organizations",
               "id":"1"
            }
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific Magento order item record.

{@inheritdoc}

### delete_list

Delete a collection of records represented by Magento order items.

{@inheritdoc}

## FIELDS

### name

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### qty

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

## SUBRESOURCES

### order

#### get_subresource

Retrieve the record of a Magento order to which a specific Magento order item belongs.

#### get_relationship

Retrieve the ID of a Magento order record to which a specific Magento order item belongs.

#### update_relationship

Replace the Magento order to which a specific Magento order item belongs.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentoorders",
    "id": "21"
  }
}
```
{@/request}

### owner

#### get_subresource

Retrieve the record of an organization to which a specific Magento order item belongs.

#### get_relationship

Retrieve the ID of the organization record to which a specific Magento order item belongs.

#### update_relationship

Replace the organization to which a specific Magento order item belongs.

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

### channel

#### get_subresource

Retrieve an integration channel via which information about the Magento order item is received.

#### get_relationship

Retrieve the ID of an integration channel via which information about the Magento order item is received.

#### update_relationship

Replace an integration channel via which information about the Magento order item is received.

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
