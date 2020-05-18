# Oro\Bundle\MagentoBundle\Entity\CartAddress

## ACTIONS  

### get

Retrieve a specific Magento cart address record.

{@inheritdoc}

### get_list

Retrieve a collection of records represented by Magento cart addresses.

{@inheritdoc}

### create

Create a new Magento cart address record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
  "data":{
    "type":"magentocartaddresses"
    "attributes":{
      "street":"Lake",
      "city":"Gurdiff",
      "postalCode":"05246",
      "namePrefix":"Dr.",
      "firstName":"Yougin",
      "middleName":"Albert",
      "lastName":"Martin",
      "phone":"+14569453",
      "primary":true,
      "organization":"Sales Corp",
      "originId":"1245"
    },
    "relationships":{
      "country":{  
        "data":{
          "type":"countries",
          "id":"US"
        }
      },
      "region":{  
        "data":{
          "type":"regions",
          "id":"US-MI"
        }
      }
    }
  }
}
```
{@/request}

### update

Edit a specific Magento cart address record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{  
  "data":{
    "type":"magentocartaddresses",
    "id":"4",
    "attributes":{
      "street":"Lake",
      "city":"Gurdiff",
      "postalCode":"05246",
      "namePrefix":"Dr.",
      "firstName":"Yougin",
      "middleName":"Albert",
      "lastName":"Martin",
      "phone":"+14569453",
      "organization":"Sales Corp",
      "originId":"1245"
    },
    "relationships":{
      "country":{
        "data":{
          "type":"countries",
          "id":"US"
        }
      },
      "region":{
        "data":{
          "type":"regions",
          "id":"US-MI"
        }
      }
    }
  }
}
```
{@/request}

### delete

Delete a specific Magento cart address record.

{@inheritdoc}

### delete_list

Delete a collection of Magento cart addresses records.

{@inheritdoc}

## SUBRESOURCES

### country

#### get_subresource

Retrieve the record of the country configured for a specific Magento cart address record.

#### get_relationship

Retrieve the ID of the country configured for a specific Magento cart address record.

#### update_relationship

Replace the country configured for a specific Magento cart address record.

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

### region

#### get_subresource

Retrieve the record of the region configured for a specific Magento cart address record.

#### get_relationship

Retrieve the ID of the region configured for a specific Magento cart address record.

#### update_relationship

Replace the region configured for a specific Magento cart address record.

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

### channel

#### get_subresource

Retrieve an integration channel via which information about the Magento cart address is received.

#### get_relationship

Retrieve the ID of an integration channel via which information about the Magento cart address is received.

#### update_relationship

Replace an integration channel via which information about the Magento cart address is received.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "integrationchannels",
    "id": "1"
  }
}
```
{@/request}
