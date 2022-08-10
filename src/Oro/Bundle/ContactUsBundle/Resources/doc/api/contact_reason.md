# Oro\Bundle\ContactUsBundle\Entity\ContactReason

## ACTIONS

### get

Retrieve a specific contact reason record.

{@inheritdoc}

### get_list

Retrieve a collection of contact reason records.

{@inheritdoc}

### create

Create a new contact reason record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "contactreasons",
    "relationships": {
      "labels": {
        "data": [
          {
            "type": "localizedfallbackvalues",
            "id": "new_label"
          }
        ]
      }
    }
  },
  "included": [
    {
      "type": "localizedfallbackvalues",
      "id": "new_label",
      "attributes": {
        "string": "Some reason"
      }
    }
  ]
}
```
{@/request}

### update

Edit a specific contact reason record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "contactreasons",
      "id": "45",
      "relationships": {
         "labels": {
           "data": [
             {
               "type": "localizedfallbackvalues",
               "id": "567"
             },
             {
               "type": "localizedfallbackvalues",
               "id": "568"
             }
           ]
         }
      }
   }
}
```
{@/request}

### delete

Deactivate a specific contact reason record.

{@inheritdoc}

### delete_list

Deactivate a collection of contact reason records.

{@inheritdoc}

## FIELDS

### deactivatedAt

#### create, update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

## SUBRESOURCES

### labels

#### get_subresource

Retrieve the records for the labels of a specific contact reason record.

#### get_relationship

Retrieve a list of IDs for the labels of a specific contact reason record.

#### add_relationship

Set the labels of a specific contact reason record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "localizedfallbackvalues",
      "id": "1"
    }
  ]
}
```
{@/request}

#### update_relationship

Replace the labels for a specific contact reason record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "localizedfallbackvalues",
      "id": "1"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove the labels of a specific contact reason record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "localizedfallbackvalues",
      "id": "1"
    }
  ]
}
```
{@/request}
