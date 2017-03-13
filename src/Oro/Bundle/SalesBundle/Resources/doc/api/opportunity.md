# Oro\Bundle\SalesBundle\Entity\Opportunity

## ACTIONS

### get

Get one Opportunity record.

{@inheritdoc}

### get_list

Get the list of Opportunity records.

{@inheritdoc}

### create

Create a new Opportunity record.
The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

`</api/opportunities>`

```JSON
{  
   "data":{  
      "type":"opportunities",
      "attributes":{  
         "name":"Roy Greenwell",
         "budgetAmountCurrency":"USD",
         "budgetAmountValue":"5765.0000"
      },
      "relationships":{  
         "contact":{  
            "data":{  
               "type":"contacts",
               "id":"2"
            }
         },
         "owner":{  
            "data":{  
               "type":"users",
               "id":"43"
            }
         },
         "organization":{  
            "data":{  
               "type":"organizations",
               "id":"1"
            }
         },
         "customer":{
            "data":{  
               "type":"b2bcustomers",
               "id":"9"
            }
         },
         "account":{
            "data":{
               "type":"accounts",
               "id":"3"
            }
         },
         "status":{  
            "data":{  
               "type":"opportunitystatuses",
               "id":"in_progress"
            }
         }
      }
   }
}
```
{@/request}

### update

Update existing Opportunity record.
The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

`</api/opportunities/52>`

```JSON
{  
   "data":{  
      "type":"opportunities",
      "id":"52",
      "attributes":{  
         "name":"Roy Greenwell",
         "budgetAmountCurrency":"USD",
         "budgetAmountValue":"5765.0000"
      },
      "relationships":{  
         "contact":{  
            "data":{  
               "type":"contacts",
               "id":"2"
            }
         },
         "owner":{  
            "data":{  
               "type":"users",
               "id":"43"
            }
         },
         "organization":{  
            "data":{  
               "type":"organizations",
               "id":"1"
            }
         },
         "customer":{
            "data":{  
               "type":"b2bcustomers",
               "id":"9"
            }
         },
         "account":{
            "data":{
               "type":"accounts",
               "id":"3"
            }
         },
         "status":{  
            "data":{  
               "type":"opportunitystatuses",
               "id":"in_progress"
            }
         }
      }
   }
}
```
{@/request}

### delete

Delete existing Opportunity record.

{@inheritdoc}

### delete_list

Delete existing Opportunity records.
The list of records that will be deleted, could be limited by filters.

{@inheritdoc}

## FIELDS

### name

#### create

{@inheritdoc}

**The required field**

#### update 

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### customer

The customer the opportunity is assigned to.

#### create

The customer the opportunity is assigned to (**required** if no account is specified).

### account

The account the opportunity is assigned to.

#### create

The account the opportunity is assigned to (**required** if no customer is specified, taken from customer if it is). If customer field is specified, this account have to relate to the customer.

### status

#### create

{@inheritdoc}

**The required field**

## SUBRESOURCES

### closeReason

#### get_subresource

Get full information about the reason for opportunity closure.

#### get_relationship

Get the reason for opportunity closure.

#### update_relationship

Update the reason for opportunity closure.

{@request:json_api}
Example:

`</api/opportunities/45/relationships/closeReason>`

```JSON
{
  "data": {
    "type": "opportunityclosereasons",
    "id": "outsold"
  }
}
```
{@/request}

### contact

#### get_subresource

Get full information about the person on the customer side who is directly related to the opportunity.

#### get_relationship

Get the person on the customer side who is directly related to the opportunity.

#### update_relationship

Update the person on the customer side who is directly related to the opportunity.

{@request:json_api}
Example:

`</api/opportunities/1/relationships/contact>`

```JSON
{
  "data": {
    "type": "contacts",
    "id": "2"
  }
}
```
{@/request}

### customer

#### get_subresource

Get full information about a customer the opportunity is created for.

#### get_relationship

Get a customer the opportunity is created for.

#### update_relationship

Update a customer the opportunity is created for.

### account

#### get_subresource

Get full information about an account the opportunity is created for.

#### get_relationship

Get an account the opportunity is created for.

#### update_relationship

Update an account the opportunity is created for.

### lead

#### get_subresource

Get full information about the sale prospect that has been successfully qualified into this opportunity.

#### get_relationship

Get the sale prospect that has been successfully qualified into this opportunity.

#### update_relationship

Update the sale prospect that has been successfully qualified into this opportunity.

{@request:json_api}
Example:

`</api/opportunities/54/relationships/lead>`

```JSON
{
  "data": {
    "type": "leads",
    "id": "1"
  }
}
```
{@/request}

### organization

#### get_subresource

Get full information about an organization to which the opportunity belongs.

#### get_relationship

Get an organization to which the opportunity belongs.

#### update_relationship

Update an organization to which the opportunity belongs.

{@request:json_api}
Example:

`</api/opportunities/1/relationships/organization>`

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

Get full information about an user who owns the opportunity.

#### get_relationship

Get an user who owns the opportunity

#### update_relationship

Update an user who owns the opportunity.

{@request:json_api}
Example:

`</api/opportunities/1/relationships/owner>`

```JSON
{
  "data": {
    "type": "users",
    "id": "43"
  }
}
```
{@/request}

### status

#### get_subresource

Get full information about a stage in the process of a sale.

#### get_relationship

Get a stage in the process of a sale.

#### update_relationship

Update a stage in the process of a sale.

{@request:json_api}
Example:

`</api/opportunities/1/relationships/status>`

```JSON
{
  "data": {
    "type": "opportunitystatuses",
    "id": "in_progress"
  }
}
```
{@/request}

# Extend\Entity\EV_Opportunity_Status

## ACTIONS  

### get

Retrieve a specific opportunity status record MD.

{@inheritdoc}

### get_list

Retrieve a collection of opportunity statuses MD.

{@inheritdoc}
