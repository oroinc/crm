# Oro\Bundle\ContactBundle\Entity\Contact

## FIELDS

### emails

An array of email addresses.

The **email** property is a string contains an email address.

Example of data: **\[{"email": "first@email.com"}, {"email": "second@email.com"}\]**

#### create

{@inheritdoc}

**Note:**
Data should contain all email addresses of the contact, including the primary email address.

**Conditionally required field:**
Either **firstName**, **lastName**, **emails** or **phones** must be defined.

#### update

{@inheritdoc}

**Note:**
Data should contain all email addresses of the contact, including the primary email address.

**Conditionally required field:**
Either **firstName**, **lastName**, **emails** or **phones** must remain defined.

### phones

An array of phone numbers.

The **phone** property is a string contains a phone number.

Example of data: **\[{"phone": "202-555-0141"}, {"phone": "202-555-0171"}\]**

#### create

{@inheritdoc}

**Note:**
Data should contain all phone numbers of the contact, including the primary phone number.

**Conditionally required field:**
Either **firstName**, **lastName**, **emails** or **phones** must be defined.

#### update

{@inheritdoc}

**Note:**
Data should contain all phone numbers of the contact, including the primary phone number.

**Conditionally required field:**
Either **firstName**, **lastName**, **emails** or **phones** must remain defined.

### primaryEmail

Primary email address of the contact.

#### create, update

The email address that should be set as the primary one.

**Note:**
The **emails** collection should contain the primary email address if the request has this collection.

### primaryPhone

Primary phone number of the contact.

#### create, update

The phone number that should be set as the primary one.

**Note:**
The **phones** collection should contain the primary phone number if the request has this collection.

### firstName

#### create

{@inheritdoc}

**Conditionally required field:**
Either **firstName**, **lastName**, **emails** or **phones** must be defined.

#### update

{@inheritdoc}

**Conditionally required field:**
Either **firstName**, **lastName**, **emails** or **phones** must remain defined.

### lastName

#### create

{@inheritdoc}

**Conditionally required field:**
Either **firstName**, **lastName**, **emails** or **phones** must be defined.

#### update

{@inheritdoc}

**Conditionally required field:**
Either **firstName**, **lastName**, **emails** or **phones** must remain defined.

## FILTERS

### emails

Filter records by email address.

### firstName

Filter records by first name.

### phones

Filter records by phone number.

### primaryEmail

Filter records by primary email address.

### primaryPhone

Filter records by primary phone number.

## ACTIONS  

### get

Retrieve a specific contact record.

{@inheritdoc}

### get_list

Retrieve a collection of contact records.

{@inheritdoc}

### create

Create a new contact record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "contacts",
      "attributes": {
         "firstName": "Jerry",
         "lastName": "Coleman",
         "primaryPhone": "585-255-1127",
         "primaryEmail": "JerryAColeman@armyspy.com"
      },
      "relationships": {
         "owner": {
            "data": {
               "type": "users",
               "id": "5"
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

### update

Edit a specific contact record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "contacts",
      "id": "1",
      "attributes": {
         "middleName": "Muriell",
         "lastName": "Coleman",
         "jobTitle": "CEO",
         "skype": "skype.skype"
      },
      "relationships": {
         "method": {
            "data": {
               "type": "contactmethods",
               "id": "phone"
            }
         },
         "organization": {
            "data": {
               "type": "organizations",
               "id": "1"
            }
         },
         "defaultInAccounts": {
            "data": [
               {
                  "type": "accounts",
                  "id": "1"
               },
               {
                  "type": "accounts",
                  "id": "37"
               }
            ]
         },
         "picture": {
            "data": {
               "type": "files",
               "id": "2"
            }
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific contact record.

{@inheritdoc}

### delete_list

Delete a collection of contact records.

{@inheritdoc}

## SUBRESOURCES

### accounts

#### get_subresource

Retrieve the account records a specific contact record is assigned to.

#### get_relationship

Retrieve the IDs of the account records which a specific contact record is assigned to.

#### update_relationship

Replace accounts record assigned to a specific contact record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "accounts",
      "id": "1"
    }
  ]
}
```
{@/request}

#### add_relationship

Set account records for a specific contact.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "accounts",
      "id": "2"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove account records from a specific contact.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "accounts",
      "id": "1"
    }
  ]
}
```
{@/request}

### addresses

#### get_subresource

Retrieve a record of addresses assigned to a specific contact record.

#### get_relationship

Retrieve IDs of address records assigned to a specific contact record.

### assignedTo

#### get_subresource

Retrieve the record of the user to whom a specific contact record is assigned.

#### get_relationship

Retrieve the ID of a user record which a specific contact record will be assigned to.

#### update_relationship

Replace the user a specific contact record is assigned to.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "5"
  }
}
```
{@/request}

### createdBy

#### get_subresource

Retrieve the record of the user who created a specific contact record.

#### get_relationship

Retrieve the ID of the user who created a specific contact record.

#### update_relationship

Replace the user who created a specific contact record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "47"
  }
}
```
{@/request}

### defaultInAccounts

#### get_subresource

Retrieve the account records for which a specific contact record is default.

#### get_relationship

Retrieve the IDs of the accounts for which a specific contact record is default.

#### add_relationship

Set accounts for which a specific contact record will be default.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "accounts",
      "id": "2"
    }
  ]
}
```
{@/request}

#### update_relationship

Replace accounts record for which a specific contact record is default.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "accounts",
      "id": "1"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove an account from the list of accounts for which a specific contact record is default.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "accounts",
      "id": "2"
    }
  ]
}
```
{@/request}

### groups

#### get_subresource

Retrieve the records of groups a specific contact record belongs to.

#### get_relationship

Retrieve the IDs of the groups a specific contact record belongs to.

#### add_relationship

Set groups a specific contact will belong to.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "contactgroups",
      "id": "1"
    }
  ]
}
```
{@/request}

#### update_relationship

Replace the groups a specific contact record belongs to.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "contactgroups",
      "id": "1"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove a specific contact record from the groups.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "contactgroups",
      "id": "1"
    }
  ]
}
```
{@/request}

### method

#### get_subresource

Retrieve the record of the contact method configured for a specific contact record.

#### get_relationship

Retrieve the ID of the contact method configured for a specific contact record.

#### update_relationship

Replace the contact method configured for a specific contact record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "contactmethods",
    "id": "phone"
  }
}
```
{@/request}

### organization

#### get_subresource

Retrieve the record of the organization a specific contact record belongs to.

#### get_relationship

Retrieve the ID of the organization record which a specific contact record will belong to.

#### update_relationship

Replace the organization a specific contact record belongs to.

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

Retrieve the record of the user who is an owner of a specific contact record.

#### get_relationship

Retrieve the ID of the user who is an owner of a specific contact record.

#### update_relationship

Replace the owner of a specific contact record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "5"
  }
}
```
{@/request}

### picture

#### get_subresource

Retrieve the picture configured for a specific contact record.

#### get_relationship

Retrieve the ID of the picture configured for a specific contact record.

#### update_relationship

Replace the picture for a specific contact record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "files",
    "id": "2"
  }
}
```
{@/request}

### reportsTo

#### get_subresource

Retrieve the record of the contact a specific contact record reports to.

#### get_relationship

Retieve the ID of the contact a specific contact record reports to.

#### update_relationship

Replace the contact a specific contact record reports to.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "contacts",
    "id": "1"
  }
}
```
{@/request}

### source

#### get_subresource

Retrieve the source record configured for a specific contact record.

#### get_relationship

Retrieve the source of a specific contact record.

#### update_relationship

Replace the source of a specific contact record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "contactsources",
    "id": "website"
  }
}
```
{@/request}

### updatedBy

#### get_subresource

Retrieve the record of the user who updated a specific contact record.

#### get_relationship

Retrieve the ID of the user who updated a specific contact record.

#### update_relationship

Replace the user who updated a specific contact record.

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


# Oro\Bundle\ContactBundle\Entity\Method

## ACTIONS

### get

Retrieve a collection of contact method records.

{@inheritdoc}

### get_list

Retrieve a specific contact method record.

{@inheritdoc}


# Oro\Bundle\ContactBundle\Entity\Source

## ACTIONS

### get

Retrieve a collection of contact source records.

{@inheritdoc}

### get_list

Retrieve a specific contact source record.

{@inheritdoc}
