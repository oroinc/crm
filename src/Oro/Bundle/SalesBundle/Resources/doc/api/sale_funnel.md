# Oro\Bundle\SalesBundle\Entity\SalesFunnel

## ACTIONS  

### get

Retrieve a specific sales process record.

{@inheritdoc}

### get_list

Retrieve a collection of sales processes records.

{@inheritdoc}

### create

Create a new sales process record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "salesfunnels",
      "attributes": {
         "startDate": "2017-02-21"
      },
      "relationships": {
         "owner": {
            "data": {
               "type": "users",
               "id": "1"
            }
         },
         "lead": {
            "data": {
               "type": "leads",
               "id": "31"
            }
         },
         "opportunity": {
            "data": null
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

Edit a specific sales process record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "salesfunnels",
      "id": "1",
      "attributes": {
         "startDate": "2017-02-21"
      },
      "relationships": {
         "owner": {
            "data": {
               "type": "users",
               "id": "1"
            }
         },
         "lead": {
            "data": {
               "type": "leads",
               "id": "31"
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

Delete a specific sales process record.

{@inheritdoc}

### delete_list

Delete a collection of sales processes records.

{@inheritdoc}

## FIELDS

### startDate

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### lead

#### create

{@inheritdoc}

**Conditionally required field:**
Either **lead** or **opportunity** must be defined.

### opportunity

#### create

{@inheritdoc}

**Conditionally required field:**
Either **lead** or **opportunity** must be defined.

### campaign

The marketing campaign as a result of which the sales funnel was created.

## SUBRESOURCES

### lead

#### get_subresource

Retrieve a record of a lead that belongs to a specific sales process record.

#### get_relationship

Retrieve the ID of a lead record that belongs to a specific sales process record.

#### update_relationship

Replace the lead record that belongs to a specific sales process record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "leads",
    "id": "1"
  }
}
```
{@/request}

### opportunity

#### get_subresource

Retrieve a record of an opportunity that belongs to a specific sales process record.

#### get_relationship

Retrieve the ID of an opportunity record that belongs to a specific sales process record.

#### update_relationship

Replace the opportunity record that belongs to a specific sales process record.

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

Retrieve the record of the organization a specific sales process record belongs to.

#### get_relationship

Retrieve the ID of an organization record that a sales process belongs to.

#### update_relationship

Replace the organization a specific sales process belongs to.

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

Retrieve the record of the user who is the owner of a specific sales process record.

#### get_relationship

Retrieve the ID of a user who is the owner of a specific sales process record.

#### update_relationship

Replace the owner of a specific sales process record.

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
