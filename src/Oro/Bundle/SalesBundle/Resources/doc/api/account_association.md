# %customer_entity%

## FIELDS

### %account_association%

The account associated with the %customer_entity_name% record.

#### create

The account associated with the %customer_entity_name% record.

**If not specified, a new account will be created.**

#### update

The account associated with the %customer_entity_name% record.

**The required field.**

## SUBRESOURCES

### %account_association%

#### get_subresource

Retrieve the account record associated with a specific %customer_entity_name% record.

#### get_relationship

Retrieve the ID of the account associated with a specific %customer_entity_name% record.

#### update_relationship

Replace the account that is associated with a specific %customer_entity_name% record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "accounts",
    "id": "1"
  }
}
```
{@/request}
