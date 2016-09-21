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
