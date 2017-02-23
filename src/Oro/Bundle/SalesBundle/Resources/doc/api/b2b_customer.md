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

`</api/b2bcustomers>`

```JSON
{  
   "data":{  
      "type":"b2bcustomers",
      "attributes":{  
         "name":"Life Plan Counselling East",
         "primaryPhone":"585-255-1127",
         "primaryEmail":"JerryAColeman@armyspy.com",
         "phones":[  
            {  
               "phone":"585-255-1127"
            }
         ],
         "emails":[  
            {  
               "email":"JerryAColeman@armyspy.com"
            }
         ]
      },
      "relationships":{  
         "shippingAddress":{  
            "data":{  
               "type":"addresses",
               "id":"1"
            }
         },
         "billingAddress":{  
            "data":{  
               "type":"addresses",
               "id":"2"
            }
         },
         "account":{  
            "data":{  
               "type":"accounts",
               "id":"26"
            }
         },
         "leads":{  
            "data":[  
               {  
                  "type":"leads",
                  "id":"7"
               }
            ]
         },
         "opportunities":{  
            "data":[  
               {  
                  "type":"opportunities",
                  "id":"24"
               }
            ]
         },
         "owner":{  
            "data":{  
               "type":"users",
               "id":"27"
            }
         },
         "organization":{  
            "data":{  
               "type":"organizations",
               "id":"1"
            }
         },
         "dataChannel":{  
            "data":{  
               "type":"channels",
               "id":"1"
            }
         }
      }
   }
}
```
{@/request}

### update

Edit a specific business customer record.

{@inheritdoc}

{@request:json_api}
Example:

`</api/b2bcustomers/27>`

```JSON
{  
   "data":{  
      "type":"b2bcustomers",
      "id":"27",
      "attributes":{  
         "website":"www.site.com",
         "employees":"25",
         "ownership":"founder",
         "ticker_symbol":"HPQ",
         "rating":"hight",
         "lifetime":"256.45"
      },
      "relationships":{  
         "shippingAddress":{  
            "data":{  
               "type":"addresses",
               "id":"7"
            }
         },
         "billingAddress":{  
            "data":{  
               "type":"addresses",
               "id":"5"
            }
         },
         "contact":{  
            "data":{  
               "type":"contacts",
               "id":"7"
            }
         },
         "owner":{  
            "data":{  
               "type":"users",
               "id":"28"
            }
         },
         "dataChannel":{  
            "data":{  
               "type":"channels",
               "id":"2"
            }
         }
      }
   }
}
```
{@/request}

### delete_list

Delete a collection of business customer records.
The list of records that will be deleted, could be limited by filters.

{@inheritdoc}

### delete

Delete a specific business customer record.

{@inheritdoc}

## FIELDS

### id

#### update

{@inheritdoc}

**The required field**

### emails

An array of email addresses.

Format of data: [{"email": first@email.com}, {"email": second@email.com}]

#### create, update

An array of email addresses.

Format of data: [{"email": first@email.com}, {"email": second@email.com}]

Data should contain full collection of email addresses of the business customer.

### phones

An array of phone numbers.

Format of data: [{"phone": phonenumber1}, {"phone": phonenumber2}]

#### create, update

An array of phone numbers.

Format of data: [{"phone": phonenumber1}, {"phone": phonenumber2}]

Data should contain full collection of phone numbers of the business customer.

### primaryEmail

Primary email address of the business customer.

#### create, update

The email address that should be set as the primary one.

**Please note:**

*The primary email address will be added to **emails** collection if it does not contain it yet.*

### primaryPhone

Primary phone number of the business customer.

#### create, update

The phone number that should be set as the primary one.

**Please note:**
 
*The primary phone number will be added to **phones** collection if it does not contain it yet.*

### account

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### dataChannel

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

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

### account

#### get_subresource

Retrieve the accout record that a specific business customer record is assigned to.

#### get_relationship

Retrieve the IDs of the accounts that a specific business customer is assigned to.

#### update_relationship

Replace the accounts that a specific business customer is assigned to.

{@request:json_api}
Example:

`</api/b2bcustomers/1/relationships/account>`

```JSON
{
  "data": {
    "type": "accounts",
    "id": "26"
  }
}
```
{@/request}

### billingAddress

#### get_subresource

Retrieve the record of the billing address configured for a specific business customer.

#### get_relationship

Retrieve the ID of the billing address that is configured for a specific business customer.

#### update_relationship

Replace the billing address for a specific business customer.

{@request:json_api}
Example:

`</api/b2bcustomers/1/relationships/billingAddress>`

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

`</api/b2bcustomers/27/relationships/contact>`

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

`</api/b2bcustomers/1/relationships/dataChannel>`

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

`</api/b2bcustomers/1/relationships/leads>`

```JSON
{  
   "data":[  
      {  
         "type":"leads",
         "id":"5"
      },
      {  
         "type":"leads",
         "id":"7"
      }
   ]
}
```
{@/request}

#### update_relationship

Replace the leads that a specific business customer is assigned to.

{@request:json_api}
Example:

`</api/b2bcustomers/1/relationships/leads>`

```JSON
{  
   "data":[  
      {  
         "type":"leads",
         "id":"5"
      },
      {  
         "type":"leads",
         "id":"7"
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

`</api/b2bcustomers/27/relationships/opportunities>`

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

`</api/b2bcustomers/27/relationships/opportunities>`

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

`</api/b2bcustomers/1/relationships/organization>`

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

Retrieve the record of the user who is the owner of a specific business customer.

#### get_relationship

Retrieve the ID of the user who is an owner of a specific business customer.

#### update_relationship

Replace the owner for a specific business customer.

{@request:json_api}
Example:

`</api/b2bcustomers/1/relationships/owner>`

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

`</api/b2bcustomers/1/relationships/shippingAddress>`

```JSON
{
  "data": {
    "type": "addresses",
    "id": "1"
  }
}
```
{@/request}
