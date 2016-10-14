# OroCRM\Bundle\ContactBundle\Entity\Contact

## FIELDS

### emails

An array of emails.

Format of data: [{"email": first@email.com}, {"email": second@email.com}]

#### create, update

An array of emails.

Format of data: [{"email": first@email.com}, {"email": second@email.com}]

The data should contain full collection of emails.

### phones

An array of phones.

Format of data: [{"phone": phonenumber1}, {"phone": phonenumber2}]

#### create, update

An array of phones.

Format of data: [{"phone": phonenumber1}, {"phone": phonenumber2}]

The data should contain full collection of phones.

### primaryEmail

The primary email address.

#### create, update

The email address that should be set as the primary one.

*Please note*

The primary email address will be added to **emails** collection if it does not contain it yet.

### primaryPhone

The primary phone number.

#### create, update

The phone number that should be set as the primary one.

*Please note*

The primary phone number will be added to **phones** collection if it does not contain it yet.

## FILTERS

### emails

Filter records by email address.

### phones

Filter records by phone number.

### primaryEmail

Filter records by primary email address.

### primaryPhone

Filter records by primary phone number.
