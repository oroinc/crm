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

```JSON
{
   "data": {
      "type": "contactgroups",
      "attributes": {
         "label": "Sales Pro Group"
      },
      "relationships": {
         "owner": {
            "data": {
               "type": "users",
               "id": "1"
            }
         },
         "organization": {
            "data": {
               "type": "organizations",
               "id": "1"
            }
         }
      }
   }
}
```
{@/request}

### update

Edit a specific contact group record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "contactgroups",
      "id": "1",
      "attributes": {
         "label": "Sales Group"
      },
      "relationships": {
         "owner": {
            "data": {
               "type": "users",
               "id": "1"
            }
         },
         "organization": {
            "data": {
               "type": "organizations",
               "id": "1"
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

{@inheritdoc}

## FIELDS

### label

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

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

```JSON
{
  "data": {
    "type": "users",
    "id": "1"
  }
}
```
{@/request}
