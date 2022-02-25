# Oro\Bundle\SalesBundle\Entity\B2bCustomer

## ACTIONS

### get

Retrieve a specific business customer records.

{@inheritdoc}

### get_list

Retrieve a collection of business customer records.

{@inheritdoc}

### create

Create a new business customer record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "b2bcustomers",
      "attributes": {
         "name": "Life Plan Counselling East",
         "primaryPhone": "585-255-1127",
         "primaryEmail": "JerryAColeman@armyspy.com",
         "phones": [
            {
               "phone": "585-255-1127"
            }
         ],
         "emails": [
            {
               "email": "JerryAColeman@armyspy.com"
            }
         ]
      },
      "relationships": {
         "shippingAddress": {
            "data": {
               "type": "addresses",
               "id": "1"
            }
         },
         "billingAddress": {
            "data": {
               "type": "addresses",
               "id": "2"
            }
         },
         "leads": {
            "data": [
               {
                  "type": "leads",
                  "id": "7"
               }
            ]
         },
         "opportunities": {
            "data": [
               {
                  "type": "opportunities",
                  "id": "24"
               }
            ]
         },
         "owner": {
            "data": {
               "type": "users",
               "id": "27"
            }
         },
         "organization": {
            "data": {
               "type": "organizations",
               "id": "1"
            }
         },
         "dataChannel": {
            "data": {
               "type": "channels",
               "id": "1"
            }
         }
      }
   }
}
```
{@/request}

### update

Edit a specific business customer record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "b2bcustomers",
      "id": "27",
      "attributes": {
         "website": "www.site.com",
         "employees": "25",
         "ownership": "founder",
         "ticker_symbol": "HPQ",
         "rating": "hight",
         "lifetime": "256.45"
      },
      "relationships": {
         "shippingAddress": {
            "data": {
               "type": "addresses",
               "id": "7"
            }
         },
         "billingAddress": {
            "data": {
               "type": "addresses",
               "id": "5"
            }
         },
         "contact": {
            "data": {
               "type": "contacts",
               "id": "7"
            }
         },
         "owner": {
            "data": {
               "type": "users",
               "id": "28"
            }
         },
         "dataChannel": {
            "data": {
               "type": "channels",
               "id": "2"
            }
         }
      }
   }
}
```
{@/request}

### delete_list

Delete a collection of business customer records.

{@inheritdoc}

### delete

Delete a specific business customer record.

{@inheritdoc}

## FIELDS

### emails

An array of email addresses.

The **email** property is a string contains an email address.

Example of data: **\[{"email": "first@email.com"}, {"email": "second@email.com"}\]**

#### create, update

{@inheritdoc}

**Note:**
Data should contain all email addresses of the business customer, including the primary email address.

### phones

An array of phone numbers.

The **phone** property is a string contains a phone number.

Example of data: **\[{"phone": "202-555-0141"}, {"phone": "202-555-0171"}\]**

#### create, update

{@inheritdoc}

**Note:**
Data should contain all phone numbers of the business customer, including the primary phone number.

### primaryEmail

Primary email address of the business customer.

#### create, update

The email address that should be set as the primary one.

**Note:**
The **emails** collection should contain the primary email address if the request has this collection.

### primaryPhone

Primary phone number of the business customer.

#### create, update

The phone number that should be set as the primary one.

**Note:**
The **phones** collection should contain the primary phone number if the request has this collection.

### account

The account associated with the business customer record.

#### create

The account associated with the business customer record.

**If not specified, a new account will be created.**

#### update

The account associated with the business customer record.

**The required field.**

### dataChannel

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

## FILTERS

### emails

Filter records by email address.

### phones

Filter records by phone number.

### primaryEmail

Filter records by primary email address.

### primaryPhone

Filter records by primary phone number.

## SUBRESOURCES

### billingAddress

#### get_subresource

Retrieve the record of the billing address configured for a specific business customer.

#### get_relationship

Retrieve the ID of the billing address that is configured for a specific business customer.

#### update_relationship

Replace the billing address for a specific business customer.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "addresses",
    "id": "2"
  }
}
```
{@/request}

### contact

#### get_subresource

Retrieve the record of the contact that is specified for a specific business customer.

#### get_relationship

Retrieve the ID of the contact that is assigned to a specific business customer.

#### update_relationship

Replace the contact for a specific business customer.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "contacts",
    "id": "7"
  }
}
```
{@/request}

### dataChannel

#### get_subresource

Retrieve the record of the channel via which an information about a specific business customer is received.

#### get_relationship

Retrieve the ID of the channel via which information about a specific business customer is received.

#### update_relationship

Replace the channel for a specific business customer.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "channels",
    "id": "1"
  }
}
```
{@/request}

### leads

#### get_subresource

Retrieve the records of the leads that a specific business customer is assigned to.

#### get_relationship

Retrieve the IDs of the leads that a specific business customer is assigned to.

#### add_relationship

Set the leads that a specific business customer will be assinged to.

{@request:json_api}
Example:

```JSON
{
   "data": [
      {
         "type": "leads",
         "id": "5"
      },
      {
         "type": "leads",
         "id": "7"
      }
   ]
}
```
{@/request}

#### update_relationship

Replace the leads that a specific business customer is assigned to.

{@request:json_api}
Example:

```JSON
{
   "data": [
      {
         "type": "leads",
         "id": "5"
      },
      {
         "type": "leads",
         "id": "7"
      }
   ]
}
```
{@/request}

#### delete_relationship

Remove the leads that a specific business customer is assigned to.

### opportunities

#### get_subresource

Retrieve the records of the opportunities that a specific business customer is assigned to.

#### get_relationship

Retrieve the IDs of the opportunities that a specific business customer is assigned to.

#### add_relationship

Set the opportunities that a specific business customer will be assigned to.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "opportunities",
      "id": "24"
    }
  ]
}
```
{@/request}

#### update_relationship

Replace the opportunities that a specific business customer is assigned to.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "opportunities",
      "id": "24"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove the opportunities that a specific business customer is assigned to.

### organization

#### get_subresource

Retrieve the record of the organization that a specific business customer belongs to.

#### get_relationship

Retrieve the ID of the organization that a specific business customer belongs to.

#### update_relationship

Replace the organization that a specific business customer belongs to.

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

Retrieve the record of the user who is the owner of a specific business customer record.

#### get_relationship

Retrieve the ID of the user who is an owner of a specific business customer record.

#### update_relationship

Replace the owner of a specific business customer record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "6"
  }
}
```
{@/request}

### shippingAddress

#### get_subresource

Retrieve the shipping address records configured for a specific business customer record.

#### get_relationship

Retrieve the ID of the shipping address configured for a specific business cusotmer.

#### update_relationship

Replace the shipping address for a specific business customer.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "addresses",
    "id": "1"
  }
}
```
{@/request}
