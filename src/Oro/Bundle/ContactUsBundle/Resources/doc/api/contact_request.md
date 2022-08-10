# Oro\Bundle\ContactUsBundle\Entity\ContactRequest

## ACTIONS

### get

Retrieve a specific contact request record.

{@inheritdoc}

### get_list

Retrieve a collection of contact request records.

{@inheritdoc}

## SUBRESOURCES

### organization

#### get_subresource

Retrieve the record of the organization a specific contact request record belongs to.

#### get_relationship

Retrieve the ID of the organization record which a specific contact request record belongs to.

### owner

#### get_subresource

Retrieve the record of the user who is the owner of a specific contact request record.

#### get_relationship

Retrieve the ID of the user who is the owner of a specific contact request record.
