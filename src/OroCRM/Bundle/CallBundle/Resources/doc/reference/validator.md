Call Validators
------------------

OroCRMCallBundle has validators that can be used to validate addresses and address collection.
One custom constraint is used to have one of the phones entered (chosen from drop down or free field).

### Example Of Usage

Validation configuration should be placed in file Resources/config/validation.yml in appropriate bundle.

```
OroCRM\Bundle\ContactBundle\Entity\Contact:
    properties:
        addresses:
            - Oro\Bundle\AddressBundle\Validator\Constraints\ContainsPrimary: ~
            - Oro\Bundle\AddressBundle\Validator\Constraints\UniqueAddressTypes: ~
```
