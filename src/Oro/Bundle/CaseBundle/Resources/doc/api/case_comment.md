# Oro\Bundle\CaseBundle\Entity\CaseComment

## ACTIONS  

### get

Retrieve a specific case comment record.

{@inheritdoc}

### get_list

Retrieve a collection of case comment records.

{@inheritdoc}

### create

Create a new case comment record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "casecomments",
      "attributes": {   
         "message": "Hello\n\npublic Signature"
      },
      "relationships": {
         "case": {
            "data": {
               "type": "cases",
               "id": "57"
            }
         },
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

Edit a specific case comment record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "casecomments",
      "id": "370",
      "attributes": {
         "message": "Hello\n\npublic Signature"
      },
      "relationships": {
         "case": {
            "data": {
               "type": "cases",
               "id": "57"
            }
         },
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

Delete a specific case comment record.

{@inheritdoc}

### delete_list

Delete a collection of case comment records.

{@inheritdoc}

## FIELDS

### case

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### message

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

## SUBRESOURCES

### attachment

#### get_subresource

Retrieve the record of the attachment uploaded with a specific case comment.

#### get_relationship

Retrieve the ID of the file attached to a specific case comment.

#### update_relationship

Replace the file attached to a specific case comment.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "files",
    "id": "4"
  }
}
```
{@/request}

### case 

#### get_subresource

Retrieve the record of the case a specific case comment was made on.

#### get_relationship

Retrieve the ID of the case that a specific case comment was made on.

#### update_relationship

Replace the case that a specific case comment was made on.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "cases",
    "id": "22"
  }
}
```
{@/request}

### contact

#### get_subresource

Retrieve the records of the contact who is an author of a specific case comment record.

#### get_relationship

Retrieve the ID of the contact who is an author of a specific case comment record.

#### update_relationship

Replace the contact who is an author of a specific case comment record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "contacts",
    "id": "40"
  }
}
```
{@/request}

### organization

#### get_subresource

Retrieve the record of the organization a specific case comment belongs to.

#### get_relationship

Retrieve the ID of the organization that a specific case comment belongs to.

#### update_relationship

Replace the organization that a specific case comment belongs to.

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

Retrieve the records of the user who is an owner of a specific case comment record.

This user is also considered the case comment author if the *contact* field value is not specified for the case comment.

#### get_relationship

Retrieve the ID of the user who is an owner of a specific case comment record.

#### update_relationship

Replace the owner of a specific case comment record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "14"
  }
}
```
{@/request}

### updatedBy

#### get_subresource

Retrieve the record of the user who last updated a specific case comment record.

#### get_relationship

Retrieve the ID of the user who last updated a specific case comment record.

#### update_relationship

Replace the user who last updated a specific case comment record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "45"
  }
}
```
{@/request}
