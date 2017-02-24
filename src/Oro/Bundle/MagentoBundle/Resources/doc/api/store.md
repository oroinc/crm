# Oro\Bundle\MagentoBundle\Entity\Store

## ACTIONS  

### get

Retrieve a specific Magento store record.

{@inheritdoc}

### get_list

Retrieve a collection of Magento store records.

{@inheritdoc}

### create

Create a new Magento store record.
The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

`</api/magentostores>`

```JSON
{  
   "data":{  
      "type":"magentostores",
      "attributes":{  
         "code":"str_main",
         "name":"Main Store",
         "originId":15475
      },
      "relationships":{  
         "website":{  
            "data":{  
               "type":"magentowebsites",
               "id":"1"
            }
         }
      }
   }
}
```
{@/request}

### update

Edit a specific Magento store record.

{@inheritdoc}

{@request:json_api}
Example:

`</api/magentostores/1>`

```JSON
{  
   "data":{  
      "type":"magentostores",
      "id":"1",
      "attributes":{  
         "code":"str_main",
         "name":"Main Store",
         "originId":15475
      },
      "relationships":{  
         "website":{  
            "data":{  
               "type":"magentowebsites",
               "id":"1"
            }
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific Magento store record.

{@inheritdoc}

### delete_list

Delete a collection of Magento store records.
The list of records that will be deleted, could be limited by filters.

{@inheritdoc}

## FIELDS

### id

#### update

{@inheritdoc}

**The required field**

### website

#### create

{@inheritdoc}

**The required field**

### code

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### name

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

## SUBRESOURCES

### website

#### get_subresource

Retrieve the  record of a Magento website assigned to a specific Magento store record.

#### get_relationship

Retrieve the ID of the website record assigned to a specific Magento store record.

#### update_relationship

Replace the record of a Magento website assigned to a specific Magento store record.

{@request:json_api}
Example:

`</api/magentostores/1/relationships/website>`

```JSON
{
  "data": {
    "type": "magentowebsites",
    "id": "1"
  }
}
```
{@/request}
