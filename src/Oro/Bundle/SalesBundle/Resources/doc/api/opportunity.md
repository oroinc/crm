# OroCRM\Bundle\SalesBundle\Entity\Opportunity

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

### update

Update existing Opportunity record.
The updated record is returned in the response.

{@inheritdoc}

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

### customer

#### create

{@inheritdoc}

**The required field**

### dataChannel

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

Get full information about a channel from which the application will get information on this opportunity.

#### get_relationship

Get a channel from which the application will get information on this opportunity.

#### update_relationship

Update a channel from which the application will get information on this opportunity.

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

