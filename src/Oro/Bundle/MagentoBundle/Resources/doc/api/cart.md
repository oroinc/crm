# Oro\Bundle\MagentoBundle\Entity\Cart

## ACTIONS  

### get

Retrieve a specific Magento shopping cart record.

{@inheritdoc}

### get_list

Retrieve a collection of records represented by Magento shopping carts. 

{@inheritdoc}

### create

Create a new Magento shopping cart record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentocarts",
      "id":"1",
      "attributes":{  
         "itemsQty":2,
         "itemsCount":2,
         "baseCurrencyCode":"USD",
         "storeCurrencyCode":"USD",
         "quoteCurrencyCode":"USD",
         "storeToBaseRate":1,
         "storeToQuoteRate":1,
         "email":"DonaldESchiller@einrot.com",
         "isGuest":false,
         "subTotal":"205.6619",
         "grandTotal":"215.6619",
         "taxAmount":"15.9019",
         "createdAt":"2017-02-13T15:42:19Z",
         "updatedAt":"2017-02-13T15:42:19Z",
         "originId":0,
         "firstName":"Donald",
         "lastName":"Schiller"
      },
      "relationships":{  
         "cartItems":{  
            "data":[  
               {  
                  "type":"magentocartitems",
                  "id":"1"
               },
               {  
                  "type":"magentocartitems",
                  "id":"2"
               }
            ]
         },
         "customer":{  
            "data":{  
               "type":"magentocustomers",
               "id":"18"
            }
         },
         "store":{  
            "data":{  
               "type":"magentostores",
               "id":"1"
            }
         },
         "status":{  
            "data":{  
               "type":"magentocartstatuses",
               "id":"open"
            }
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

Edit a specific Magento shopping cart record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentocarts",
    "id": "546",
    "attributes": {
           "itemsQty": 2,
      "itemsCount": 2,
      "baseCurrencyCode": "USD",
      "storeCurrencyCode": "USD",
      "quoteCurrencyCode": "USD",
      "storeToBaseRate": 1,
      "storeToQuoteRate": 1,
      "email": "DonaldESchiller@einrot.com",
      "giftMessage": null,
      "isGuest": false,
      "paymentDetails": null,
      "notes": null,
      "statusMessage": null,
      "importedAt": null,
      "syncedAt": null,
      "subTotal": 205.66,
      "grandTotal": 215.66,
      "taxAmount": 15.9,
      "createdAt": "2017-02-13T15:42:19Z",
      "updatedAt": "2017-02-13T15:42:19Z",
      "originId": 0,
      "firstName": "Donald",
      "lastName": "Schiller"
    },
    "relationships": {
      "cartItems": {
        "data": [
          {
            "type": "magentocartitems",
            "id": "1"
          },
          {
            "type": "magentocartitems",
            "id": "2"
          }
        ]
      },
      "customer": {
        "data": {
          "type": "magentocustomers",
          "id": "18"
        }
      },
      "store": {
        "data": {
          "type": "magentostores",
          "id": "1"
        }
      },
      "shippingAddress": {
        "data": null
      },
      "billingAddress": {
        "data": null
      },
      "status": {
        "data": {
          "type": "magentocartstatuses",
          "id": "open"
        }
      },
      "opportunity": {
        "data": null
      },
      "owner": {
        "data": {
          "type": "users",
          "id": "21"
        }
      },
      "organization": {
        "data": {
          "type": "organizations",
          "id": "1"
        }
      },
      "dataChannel": {
        "data": {
          "type": "channels",
          "id": "2"
        }
      }
    }
  }
}
```
{@/request}

### delete

Delete a specific Magento shopping cart record.

{@inheritdoc}

### delete_list

Delete a collection of Magento shopping cartrecords.

{@inheritdoc}

## FIELDS

### baseCurrencyCode

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### storeCurrencyCode

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### quoteCurrencyCode

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### storeToBaseRate

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### storeToQuoteRate

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### isGuest

#### create

{@inheritdoc}

**The required field.**

### dataChannel

#### create

{@inheritdoc}

**The required field.**

### itemsQty

#### create

{@inheritdoc}

**The required field.**

### itemsCount

#### create

{@inheritdoc}

**The required field.**

## SUBRESOURCES

### billingAddress

#### get_subresource

Retrieve a billing address record configured for a specific Magento shopping cart record.

#### get_relationship

Retrieve the ID of the billing address configured for a specific Magento shopping cart record.

#### update_relationship

Replace the billing address for a specific Magento shopping cart record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentocartaddresses",
    "id": "5"
  }
}
```
{@/request}

### cartItems

#### get_subresource

Retrieve records of cart items the belong to a specific Magento shopping cart record.

#### get_relationship

Retrieve the IDs of cart items assigned to a specific Magento shopping cart record.

#### add_relationship

Set the cart items that will be assigned to a specific Magento shopping cart record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "magentocartitems",
      "id": "1"
    },
    {
      "type": "magentocartitems",
      "id": "2"
    }
  ]
}
```
{@/request}

#### update_relationship

Replace the cart items that are assigned to a specific Magento shopping cart record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "magentocartitems",
      "id": "1"
    },
    {
      "type": "magentocartitems",
      "id": "2"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove the cart items that are assigned to a specific Magento shopping cart record.


### customer

#### get_subresource

Retrieve records of  a Magento customer to whom a specific Magento shopping cart belongs.

#### get_relationship

Retrieve the ID of the Magento customer to whom a specific Magento shopping cart record belongs.

#### update_relationship

Replace the Magento customer to whom a specific Magento shopping cart record belongs.

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

Retrieve channel record to which a specific Magento shopping cart is assigned.

#### get_relationship

Retrieve the ID of the channel via which information about a specific Magento shopping cart is received.

#### update_relationship

Replace the channel for a specific Magento shopping cart record.

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

### opportunity

#### get_subresource

Retrieve a record of an opportunity that was converted from a specific Magento shopping cart record.

#### get_relationship

Retrieve the ID of an opportunity record converted from a specific Magento shopping cart record.

#### update_relationship

Replace the opportunity record assigned to a Magento shopping cart record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "opportunities",
    "id": "1"
  }
}
```
{@/request}

### organization

#### get_subresource

Retrieve a record of an organization that a specificMagento shopping cart record belongs to.

#### get_relationship

Retrieve the ID of an organization record that a Magento shopping cart record belongs to.

#### update_relationship

Replace the organization a specific Magento shopping cart record belongs to.

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

Retrieve a record of the user who is the owner of a specific Magento shopping cart record.

#### get_relationship

Retrieve the ID of a user who is the owner of a specific Magento shopping cart record.

#### update_relationship

Replace the owner of a specific Magento shopping cart record.

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

Retrieve the store record assigned to a specific Magento shopping cart record.

#### get_relationship

Retrieve the ID of the store from which a specific Magento shopping cart has been received.

#### update_relationship

Replace the store from which a specific Magento shopping cart has been received.

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

Retrieve the status record configured for a specific Magento shopping cart record.

#### get_relationship

Retrieve the ID of the status record configured for a specific Magento shopping cart record.

#### update_relationship

Replace the status of a specific Magento shopping cart record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentocartstatuses",
    "id": "open"
  }
}
```
{@/request}

### shippingAddress

#### get_subresource

Retrieve the shipping address configured for a specific Magento shopping cart record.

#### get_relationship

Retrieve the ID of the shipping address configured for a specific Magento shopping cart record.

#### update_relationship

Replace the shipping address for a specific Magento shopping cart record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "magentocartaddresses",
    "id": "1"
  }
}
```
{@/request}

### channel

#### get_subresource

Retrieve an integration channel via which information about the Magento shopping cart is received.

#### get_relationship

Retrieve the ID of an integration channel via which information about the Magento shopping cart is received.

#### update_relationship

Replace an integration channel via which information about the Magento shopping cart is received.

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


# Oro\Bundle\MagentoBundle\Entity\CartStatus

## ACTIONS

### get

Retrieve a collection of Magento shopping cart status records.

{@inheritdoc}

### get_list

Retrieve a record of a specific Magento shopping cart status record.

{@inheritdoc}
