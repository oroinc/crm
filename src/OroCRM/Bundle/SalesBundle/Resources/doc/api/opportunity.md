# OroCRM\Bundle\SalesBundle\Entity\Opportunity

## ACTIONS

### get

Get one Opportunity record.

The Opportunity represent highly probable potential or actual sales to a new or established customer.

### get_list

Get the list of Opportunity records.

The Opportunity represent highly probable potential or actual sales to a new or established customer.

### create

Create a new Opportunity record.
The created record is returned in the response.

The Opportunity represent highly probable potential or actual sales to a new or established customer.

### update

Update existing Opportunity record.
The updated record is returned in the response.

The Opportunity represent highly probable potential or actual sales to a new or established customer.

### delete

Delete existing Opportunity record.

The Opportunity represent highly probable potential or actual sales to a new or established customer.

### delete_list

Delete existing Opportunity records.
The list of records that will be deleted, could be limited by filters.

The Opportunity represent highly probable potential or actual sales to a new or established customer.

## FIELDS

### name

The name used to refer to the opportunity in the system.

#### create, update

The name used to refer to the opportunity in the system.

**The required field**

### customer

A B2B customer the opportunity is created for.

#### create, update

A B2B customer the opportunity is created for.

**The required field**

### dataChannel

One of active channels, from which OroCRM will get information on this opportunity.
 
#### create, update

One of active channels, from which OroCRM will get information on this opportunity.

**The required field**

## SUBRESOURCES

### closeReason

#### get_subresource

Get full information about the reason for opportunity closure.

#### get_relationship

Get the reason for opportunity closure.

#### update_relationship

Update the reason for opportunity closure.

### contact

#### get_subresource

Get full information about the person on the customer side who is directly related to the opportunity.

#### get_relationship

Get the person on the customer side who is directly related to the opportunity.

#### update_relationship

Update the person on the customer side who is directly related to the opportunity.

### customer

#### get_subresource

Get full information about a B2B customer the opportunity is created for.

#### get_relationship

Get a B2B customer the opportunity is created for.

#### update_relationship

Update a B2B customer the opportunity is created for.

### dataChannel

#### get_subresource

Get full information about a channel from which OroCRM will get information on this opportunity.

#### get_relationship

Get a channel from which OroCRM will get information on this opportunity.

#### update_relationship

Update a channel from which OroCRM will get information on this opportunity.

### lead

#### get_subresource

Get full information about the sale prospect that has been successfully qualified into this opportunity.

#### get_relationship

Get the sale prospect that has been successfully qualified into this opportunity.

#### update_relationship

Update the sale prospect that has been successfully qualified into this opportunity.

### organization

#### get_subresource

Get full information about an organization to which the opportunity belongs.

#### get_relationship

Get an organization to which the opportunity belongs.

#### update_relationship

Update an organization to which the opportunity belongs.

### owner

#### get_subresource

Get full information about an user who owns the opportunity.

#### get_relationship

Get an user who owns the opportunity

#### update_relationship

Update an user who owns the opportunity.

### status

#### get_subresource

Get full information about a stage in the process of a sale.

#### get_relationship

Get a stage in the process of a sale.

#### update_relationship

Update a stage in the process of a sale.

