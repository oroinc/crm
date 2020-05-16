# Oro\Bundle\MagentoBundle\Entity\Website

## ACTIONS  

### get

Retrieve a specific Magento website record.

{@inheritdoc}

### get_list

Retrieve a collection of Magento website records.

{@inheritdoc}

### create

Create a new Magento website record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentowebsites",
      "id":"1",
      "attributes":{  
         "code":"site_main",
         "name":"GalaSales",
         "sortOrder":0,
         "default":true,
         "defaultGroupId":1
      }
   }
}
```
{@/request}

### update

Edit a specific Magento website record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
   "data":{  
      "type":"magentowebsites",
      "id":"1",
      "attributes":{  
         "code":"site_main",
         "name":"GalaSales",
         "sortOrder":0,
         "default":true,
         "defaultGroupId":1
      }
   }
}
```
{@/request}

### delete

Delete a specific Magento website record.

{@inheritdoc}

### delete_list

Delete a collection of Magento website records.

{@inheritdoc}

## FIELDS

### code

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

## SUBRESOURCES

### channel

#### get_subresource

Retrieve an integration channel via which information about the Magento website is received.

#### get_relationship

Retrieve the ID of an integration channel via which information about the Magento website is received.

#### update_relationship

Replace an integration channel via which information about the Magento website is received.

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
