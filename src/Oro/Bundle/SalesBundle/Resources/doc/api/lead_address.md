# Oro\Bundle\SalesBundle\Entity\LeadAddress

## ACTIONS  

### get

Retrieve a specific lead address record.

{@inheritdoc}

### get_list

Retrieve a collection of lead address records.

{@inheritdoc}

### create

Create a new lead address record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "leadaddresses",
      "attributes": {
         "primary": true,
         "label": "Primary Address",
         "street": "873 John Avenue",
         "city": "Jackson",
         "postalCode": "49201",
         "firstName": "Ramona",
         "lastName": "Venters"
      },
      "relationships": {
         "owner": {
            "data": {
               "type": "leads",
               "id": "1"
            }
         },
         "country": {
            "data": {
               "type": "countries",
               "id": "US"
            }
         },
         "region": {
            "data": {
               "type": "regions",
               "id": "US-MI"
            }
         }
      }
   }
}
```
{@/request}

### update

Edit a specific lead address record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "leadaddresses",
      "id" : "106",
      "attributes": {
         "primary": true,
         "label": "Primary Address",
         "street": "873 John Avenue",
         "city": "Jackson",
         "postalCode": "49201",
         "firstName": "Ramona",
         "lastName": "Venters"
      },
      "relationships": {
         "owner": {
            "data": {
               "type": "leads",
               "id": "1"
            }
         },
         "country": {
            "data": {
               "type": "countries",
               "id": "US"
            }
         },
         "region": {
            "data": {
               "type": "regions",
               "id": "US-MI"
            }
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific lead address record.

{@inheritdoc}

### delete_list

Delete a collection of lead address records.

{@inheritdoc}

## FIELDS

### country

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### owner

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

## SUBRESOURCES

### country

#### get_subresource

Retrieve the record of the country configured for a specific lead address record.

#### get_relationship

Retrieve the ID of the country configured for a specific lead address record.

#### update_relationship

Replace the country configured for a specific lead address record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "countries",
    "id": "US"
  }
}
```
{@/request}

### owner

#### get_subresource

Retrieve the record of the lead who is the owner of a specific lead address record.

#### get_relationship

Retrieve the ID of the lead who is the owner of a specific lead address record.

#### update_relationship

Replace the owner of a specific lead address record.

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

### region

#### get_subresource

Retrieve the record of the region configured for a specific lead address record.

#### get_relationship

Retrieve the ID of the region that is configured for a specific lead address record.

#### update_relationship

Replace the region that is configured for a specific lead address record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "regions",
    "id": "US-MI"
  }
}
```
{@/request}
