# Oro\Bundle\AccountBundle\Entity\Account

## ACTIONS

### get

Retrieve a specific account record.

{@inheritdoc}

### get_list

Retrieve a collection of account records.

{@inheritdoc}

### create

Create a new account record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "accounts",
      "attributes": {
         "extend_description": null,
         "name": "Gartner management group"
      },
      "relationships": {
         "owner": {
            "data": {
               "type": "users",
               "id": "44"
            }
         },
         "contacts": {
            "data": [
               {
                  "type": "contacts",
                  "id": "1"
               },
               {
                  "type": "contacts",
                  "id": "3"
               },
               {
                  "type": "contacts",
                  "id": "22"
               }
            ]
         },
         "defaultContact": {
            "data": {
               "type": "contacts",
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

Edit a specific account record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "accounts",
      "id": "51",
      "attributes": {
         "extend_description": null,
         "name": "Life Plan Counselling"
      },
      "relationships": {
         "owner": {
            "data": {
               "type": "users",
               "id": "44"
            }
         },
         "contacts": {
            "data": [
               {
                  "type": "contacts",
                  "id": "1"
               },
               {
                  "type": "contacts",
                  "id": "3"
               },
               {
                  "type": "contacts",
                  "id": "22"
               }
            ]
         },
         "defaultContact": {
            "data": {
               "type": "contacts",
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

Delete a specific account record.

{@inheritdoc}

### delete_list

Delete a collection of account records.

{@inheritdoc}

## FIELDS

### name

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

## SUBRESOURCES

### contacts

#### get_subresource

Retrieve contact records assigned to a specific account record.

#### get_relationship

Retrieve contact IDs assigned to a specific account record.

#### add_relationship

Set contacts records for a specific account record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "contacts",
      "id": "1"
    },
    {
      "type": "contacts",
      "id": "3"
    },
    {
      "type": "contacts",
      "id": "22"
    }
  ]
}
```
{@/request}

#### update_relationship

Replace the list of contacts assigned to a specific account record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "contacts",
      "id": "1"
    },
    {
      "type": "contacts",
      "id": "3"
    },
    {
      "type": "contacts",
      "id": "22"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove contact records from a specific account record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "contacts",
      "id": "1"
    }
  ]
}
```
{@/request}

### defaultContact

#### get_subresource

Retrieve the contact record that is default for a specific account record.

#### get_relationship

Retrieve the ID of the default contact assigned to a specific account record.

#### update_relationship

Replace the default contact record assigned to a specific account record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "contacts",
    "id": "1"
  }
}
```
{@/request}

### organization

#### get_subresource

Retrieve the record of the organization a specific account belongs to.

#### get_relationship

Retrieve the ID of the organization record that a specific account record belongs to.

#### update_relationship

Replace the organization a specific account belongs to.

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

Retrieve the record of the user who is the owner of a specific lead record.

#### get_relationship

Retrieve the ID of a user who is the owner of a specific account record.

#### update_relationship

Replace the owner of a specific account record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "44"
  }
}
```
{@/request}

### referredBy

#### get_subresource

Retrieve the account records that refer to a specific account record.

**Note:**
This relationship is currently unavailable via the OroCRM interface.

#### get_relationship

Retrieve the IDs of account records that refer to a specific account record.

**Note:**
This relationship is currently unavailable via the OroCRM interface.

#### update_relationship

Replace the account records that refer to a specific account record.

**Note:**
This relationship is currently unavailable via the OroCRM interface.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "accounts",
    "id": "2"
  }
}
```
{@/request}
