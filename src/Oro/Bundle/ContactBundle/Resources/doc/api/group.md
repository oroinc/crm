# Oro\Bundle\ContactBundle\Entity\Group

## ACTIONS  

### get

Retrieve a specific contact group record.

{@inheritdoc}

### get_list

Retrieve a collection of contact group records.

{@inheritdoc}

### create

Create a new contact group record.
The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

`</api/contactgroups>`

```JSON
{  
   "data":{  
      "type":"contactgroups",
      "attributes":{  
         "label":"Sales Pro Group"
      },
      "relationships":{  
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
         }
      }
   }
}
```
{@/request}

### update

Edit a specific contact group record.

{@inheritdoc}

{@request:json_api}
Example:

`</api/contactgroups/1>`

```JSON
{  
   "data":{  
      "type":"contactgroups",
      "id":"1",
      "attributes":{  
         "label":"Sales Group"
      },
      "relationships":{  
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
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific contact group record.

{@inheritdoc}

### delete_list

Delete a collection of contact group records.
The list of records that will be deleted, could be limited by filters.

{@inheritdoc}

## FIELDS

### id

#### update

{@inheritdoc}

**The required field**

### label

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

## SUBRESOURCES

### organization

#### get_subresource

Retrieve the record of the organization a specific contact group record belongs to.

#### get_relationship

Retrieve the ID of the organization record which a specific contact group record belongs to.

#### update_relationship

Replace the organization a specific contact group record belongs to.

{@request:json_api}
Example:

`</api/contactgroups/1/relationships/organization>`

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

Retrieve the record of the user who is the owner of a specific contact group record.

#### get_relationship

Retrieve the ID of the user who is the owner of a specific contact group record.

#### update_relationship

Replace the owner of a specific contact group record.

{@request:json_api}
Example:

`</api/contactgroups/1/relationships/owner>`

```JSON
{
  "data": {
    "type": "users",
    "id": "1"
  }
}
```
{@/request}
