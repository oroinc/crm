# Oro\Bundle\MagentoBundle\Entity\CartItem

## ACTIONS  

### get

Retrieve a specific Magento shopping cart item record.

{@inheritdoc}

### get_list

Retrieve a collection records that represent Magento shopping cart items.

{@inheritdoc}

### create

Create a new Magento shopping cart item record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentocartitems",
      "attributes":{  
         "productId":72,
         "freeShipping":"1",
         "description":"Item from cart",
         "isVirtual":false,
         "priceInclTax":"128.7988",
         "rowTotal":"128.7988",
         "taxAmount":"9.9588",
         "productType":"simple",
         "removed":false,
         "sku":"sku-chair",
         "name":"Chair",
         "qty":1,
         "price":"118.8400",
         "discountAmount":"0.0000",
         "taxPercent":0.0838,
         "createdAt":"2017-02-15T15:42:19Z",
         "updatedAt":"2017-02-15T15:42:19Z"
      },
      "relationships":{  
         "cart":{  
            "data":{  
               "type":"magentocarts",
               "id":"1"
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

Edit a specific Magento shopping cart item record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentocartitems",
      "id":"1",
      "attributes":{  
         "productId":72,
         "freeShipping":"1",
         "description":"Item from cart",
         "isVirtual":false,
         "priceInclTax":"128.7988",
         "rowTotal":"128.7988",
         "taxAmount":"9.9588",
         "productType":"simple",
         "removed":false,
         "sku":"sku-chair",
         "name":"Chair",
         "qty":1,
         "price":"118.8400",
         "discountAmount":"0.0000",
         "taxPercent":0.0838,
         "createdAt":"2017-02-15T15:42:19Z",
         "updatedAt":"2017-02-15T15:42:19Z"
      },
      "relationships":{  
         "cart":{  
            "data":{  
               "type":"magentocarts",
               "id":"1"
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

Delete a specific Magento shopping cart item record.

{@inheritdoc}

### delete_list

Delete a collection of Magento shopping cart items records.

{@inheritdoc}

## FIELDS

### productId

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### freeShipping

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### isVirtual

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### rowTotal

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### taxAmount

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### productType

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

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

### price

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

## SUBRESOURCES

### cart

#### get_subresource

Retrieve the Magento cart record configured for a specific Magento shopping cart item record.

#### get_relationship

Retrieve the ID of the Magento cart record configured for a specific Magento shopping cart item record.

#### update_relationship

Replace the Magento cart record configured for a specific Magento shopping cart item record.

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

### owner

#### get_subresource

Retrieve the record of the organization that is the owner of a specific Magento shopping cart item record.

#### get_relationship

Retrieve the ID of the organization that is the owner of a specific Magento shopping cart item record.

#### update_relationship

Replace the owner of a specific Magento shopping cart item record.

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

Retrieve an integration channel via which information about the Magento shopping cart item is received.

#### get_relationship

Retrieve the ID of an integration channel via which information about the Magento shopping cart item is received.

#### update_relationship

Replace an integration channel via which information about the Magento shopping cart item is received.

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
