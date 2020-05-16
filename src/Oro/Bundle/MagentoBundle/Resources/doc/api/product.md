# Oro\Bundle\MagentoBundle\Entity\Product

## ACTIONS  

### get

Retrieve a specific Magento product record.

{@inheritdoc}

### get_list

Retrieve a collection of Magento product records.

{@inheritdoc}

### create

Create a new Magento product record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentoproducts",      
      "attributes":{  
         "name":"Longboard",
         "productType":"simple",
         "specialPrice":"25.600",
         "price":"27.500",
         "createdAt":"2017-02-15T15:42:19Z",
         "updatedAt":"2017-02-15T15:42:19Z",
         "originId":1
      },
      "relationships":{  
         "websites":{  
            "data":[  
               {  
                  "type":"magentowebsites",
                  "id":"1"
               },
               {  
                  "type":"magentowebsites",
                  "id":"2"
               }
            ]
         }
      }
   }
}
```
{@/request}

### update

Edit a specific Magento product record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentoproducts",
      "id":"1",
      "attributes":{  
         "name":"Longboard",
         "productType":"simple",
         "specialPrice":"25.600",
         "price":"27.500",
         "createdAt":"2017-02-15T15:42:19Z",
         "updatedAt":"2017-02-15T15:42:19Z",
         "originId":1
      },
      "relationships":{  
         "websites":{  
            "data":[  
               {  
                  "type":"magentowebsites",
                  "id":"1"
               },
               {  
                  "type":"magentowebsites",
                  "id":"2"
               }
            ]
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific Magento product record.

{@inheritdoc}

### delete_list

Delete a collection of Magento product records.

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

### productType

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

## SUBRESOURCES

### websites

#### get_subresource

Retrieve  the records of Magento websites assigned to a specific Magento product.

#### get_relationship

Retrieve the IDs of Magento websites from which information about a specific Magento product is received.

#### add_relationship

Set Magento websites from which information about a specific Magento product is received.

{@request:json_api}
Example:

```JSON
{  
   "data":[  
      {  
         "type":"magentowebsites",
         "id":"1"
      },
      {  
         "type":"magentowebsites",
         "id":"2"
      }
   ]
}
```
{@/request}

#### update_relationship

Replace Magento websites from which information about a specific Magento product is received.

{@request:json_api}
Example:

```JSON
{  
   "data":[  
      {  
         "type":"magentowebsites",
         "id":"1"
      },
      {  
         "type":"magentowebsites",
         "id":"2"
      }
   ]
}
```
{@/request}

#### delete_relationship

Remove Magento website records from a specific Magento product record.

### channel

#### get_subresource

Retrieve an integration channel via which information about the Magento product is received.

#### get_relationship

Retrieve the ID of an integration channel via which information about the Magento product is received.

#### update_relationship

Replace an integration channel via which information about the Magento product is received.

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
