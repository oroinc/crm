# OroCaseBundle

OroCaseBundle adds the Case entity to Oro applications to allow users keep records of the issues reported by customers.

The bundle provides CRUD UI for users to create and manage cases and enables users to configure system mailboxes and create cases from the incoming emails.

## Search restrictions

Search index fields `description`, `resolution` and `message` for `CaseEntity` contain no more than 255 characters
each - please, remember about that during the search by the `CaseEntity`.
