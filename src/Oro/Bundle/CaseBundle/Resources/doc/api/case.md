# Oro\Bundle\CaseBundle\Entity\CaseEntity

## ACTIONS  

### get

Retrieve a specific case record.

{@inheritdoc}

### get_list

Retrieve a collection of case records.

{@inheritdoc}

### create

Create a new case record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "cases",
      "attributes": {
         "subject": "Parvis imbutus tentabis grandia tutus",
         "description": "Pax tibi, Marce, evangelista meus. Hic requiescet corpus tuum.",
         "resolution": "Per capsulam."
      },
      "relationships": {
         "source": {
            "data": {
               "type": "casesources",
               "id": "other"
            }
         },
         "status": {
            "data": {
               "type": "casestatuses",
               "id": "open"
            }
         },
         "priority": {
            "data": {
               "type": "casepriorities",
               "id": "high"
            }
         },
         "relatedContact": {
            "data": {
               "type": "contacts",
               "id": "3"
            }
         },
         "assignedTo": {
            "data": {
               "type": "users",
               "id": "20"
            }
         },
         "owner": {
            "data": {
               "type": "users",
               "id": "35"
            }
         },
         "comments": {
            "data": [
               {
                  "type": "casecomments",
                  "id": "1"
               },
               {
                  "type": "casecomments",
                  "id": "2"
               }
            ]
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

Edit a specific case record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "cases",
      "id": "22",
      "attributes": {
         "subject": "Parvis imbutus tentabis grandia tutus",
         "description": "Pax tibi, Marce, evangelista meus. Hic requiescet corpus tuum.",
         "resolution": "Per capsulam."
      },
      "relationships": {
         "source": {
            "data": {
               "type": "casesources",
               "id": "other"
            }
         },
         "status": {
            "data": {
               "type": "casestatuses",
               "id": "open"
            }
         },
         "priority": {
            "data": {
               "type": "casepriorities",
               "id": "high"
            }
         },
         "relatedContact": {
            "data": {
               "type": "contacts",
               "id": "3"
            }
         },
         "assignedTo": {
            "data": {
               "type": "users",
               "id": "20"
            }
         },
         "owner": {
            "data": {
               "type": "users",
               "id": "35"
            }
         },
         "comments": {
            "data": [
               {
                  "type": "casecomments",
                  "id": "1"
               },
               {
                  "type": "casecomments",
                  "id": "2"
               }
            ]
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

Delete a specific case record.

{@inheritdoc}

### delete_list

Delete a collection of case records.

{@inheritdoc}

## FIELDS

### subject

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### source

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### status

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### priority

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

## SUBRESOURCES

### assignedTo

#### get_subresource

Retrieve the user record that a specific case record is assigned to.

#### get_relationship

Retrieve the ID of the user that a specific case is assigned to.

#### update_relationship

Replace the user that a specific case is assigned to.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "20"
  }
}
```
{@/request}

### comments

#### get_subresource

Retrieve the records of the comments made on a specific case.

#### get_relationship

Retrieve the IDs of the comments made on a specific case.

#### add_relationship

Set comments made on a specific case.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "casecomments",
      "id": "3"
    },
    {
      "type": "casecomments",
      "id": "4"
    }
  ]
}
```
{@/request}

#### update_relationship

Replace comments made on a specific case.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "casecomments",
      "id": "3"
    },
    {
      "type": "casecomments",
      "id": "4"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove comments made on a specific case.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "casecomments",
      "id": "1"
    }
  ]
}
```
{@/request}

### organization

#### get_subresource

Retrieve the record of the organization a specific case belongs to.

#### get_relationship

Retrieve the ID of the organization that a specific case belongs to.

#### update_relationship

Replace the organization that a specific case belongs to.

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

Retrieve the records of the user who is an owner of a specific case record.

#### get_relationship

Retrieve the ID of the user who is an owner of a specific case record.

#### update_relationship

Replace the owner of a specific case record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "35"
  }
}
```
{@/request}

### priority

#### get_subresource

Retrieve the priority record configured for a specific case record.

#### get_relationship

Retrieve the ID of the priority configured for a specific case.

#### update_relationship

Replace the priority configured for a specific case.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "casepriorities",
    "id": "high"
  }
}
```
{@/request}

### relatedAccount

#### get_subresource

Retrieve the record of the account that is related to a specific case.

#### get_relationship

Retrieve the ID of the account that is related to a specific case.

#### update_relationship

Replace the account that is related to a specific case.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "accounts",
    "id": "8"
  }
}
```
{@/request}

### relatedContact

#### get_subresource

Retrieve the record of the contact that is related to a specific case.

#### get_relationship

Retrieve the ID of the contact that is related to a specific case.

#### update_relationship

Replace the contact that is related to a specific case.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "contacts",
    "id": "3"
  }
}
```
{@/request}

### source

#### get_subresource

Retrieve the record of the source configured for a specific case record.

#### get_relationship

Retrieve the ID of the source configured for a specific case.

#### update_relationship

Replace the source configured for a specific case.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "casesources",
    "id": "email"
  }
}
```
{@/request}

### status

#### get_subresource

Retrieve the status record configured for a specific case record.

#### get_relationship

Retrieve the ID of the status configured for a specific case.

#### update_relationship

Replace the status configured for a specific case.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "casestatuses",
    "id": "closed"
  }
}
```
{@/request}


# Oro\Bundle\CaseBundle\Entity\CasePriority

## ACTIONS

### get

Retrieve a specific case priority record.

{@inheritdoc}

### get_list

Retrieve a collection of case priority records.

{@inheritdoc}


# Oro\Bundle\CaseBundle\Entity\CaseSource

## ACTIONS

### get

Retrieve a specific case source record.

{@inheritdoc}

### get_list

Retrieve a collection of case source records.

{@inheritdoc}

# Oro\Bundle\CaseBundle\Entity\CaseStatus

## ACTIONS

### get

Retrieve a specific case status record.

{@inheritdoc}

### get_list

Retrieve a collection of case status records.

{@inheritdoc}
