# OroCRM\Bundle\SalesBundle\Entity\B2bCustomer

## Fields

### emails

Array of emails. 

Format of data: [first@email.com, second@email.com]

#### Update

Array of emails. 

Format of data: [first@email.com, second@email.com]

The data should contain full collection of emails.

#### Create

Array of emails. 

Format of data: [first@email.com, second@email.com]

The data should contain full collection of emails.

### phones

Array of phone numbers. 

Format of data: [phonenumber1, phonenumber2]

#### Update

Array of phone numbers. 

Format of data: [phonenumber1, phonenumber2]

The data should contain full collection of phones.

#### Create

Array of phone numbers. 

Format of data: [phonenumber1, phonenumber2]

The data should contain full collection of phones.

### primaryEmail

Email address that should be set as the primary one.

#### Update

Email address that should be set as the primary one.

*Please note*

The **emails** field data should contain **primaryEmail** field.

If was set **primaryEmail** data without **emails** field data and entity emails collection does not have this record, 
 new email address will be automatically added to the emails collection.
 
#### Create

Email address that should be set as the primary one.

*Please note*

The **emails** field data should contain **primaryEmail** field.

If was set **primaryEmail** data without **emails** field data and entity emails collection does not have this record, 
 new email address will be automatically added to the emails collection.
 

### primaryPhone

Phone number that should be set as the primary one.

#### Update

Phone number that should be set as the primary one.

*Please note*

The **phones** field data should contain **primaryPhone** field.

If was set **primaryPhone** data without **phones** field data and entity phones collection does not have this record, 
 new email phone will be automatically added to the phones collection.
 
#### Create

Phone number that should be set as the primary one.

*Please note*

The **phones** field data should contain **primaryPhone** field.

If was set **primaryPhone** data without **phones** field data and entity phones collection does not have this record, 
 new email phone will be automatically added to the phones collection.

## Filters

### emails

Array of emails. Format of data: [first@email.com, second@email.com]

### phones

Array of phone numbers. Format of data: [phonenumber1, phonenumber2]

### primaryEmail

Primary email address

### primaryPhone

Primary phone

# OroCRM\Bundle\SalesBundle\Entity\Opportunity

## Actions

### Get

Get one Opportunity record.

Opporunity represent highly probable potential or actual sales to a new or established customer.

### Get_list

Get the list of Opportunity records.

Opporunity represent highly probable potential or actual sales to a new or established customer.

### Create

Create new Opportunity record.

Returns new created record

Opporunity represent highly probable potential or actual sales to a new or established customer.

### Update

Update existing Opportunity record.

Returns updated record.

Opporunity represent highly probable potential or actual sales to a new or established customer.

### Delete

Delete existing Opportunity record.

Opporunity represent highly probable potential or actual sales to a new or established customer.

### Delete_list

Delete list of Opportunity records. The list of records should be deleted limits by filters.

Opporunity represent highly probable potential or actual sales to a new or established customer.

## Fields

### name

The name used to refer to the opportunity in the system.

#### Create

The name used to refer to the opportunity in the system.

**The required field**

#### Update

The name used to refer to the opportunity in the system.

**Field cannot be null**

### customer

A B2B customer the opportunity is created for.

#### Create

A B2B customer the opportunity is created for.

**The required field**

#### Update

A B2B customer the opportunity is created for.

**Field cannot be null**

### dataChannel

One of active channels, from which OroCRM will get information on this opportunity.
 
#### Create

One of active channels, from which OroCRM will get information on this opportunity.

**The required field**

#### Update

One of active channels, from which OroCRM will get information on this opportunity.

**Field cannot be null**
