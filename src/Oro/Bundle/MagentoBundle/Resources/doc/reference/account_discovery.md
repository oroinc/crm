Automatic accounts discovery
============================

### Configuration Reference

```
oro_magento:
    account_discovery:
        fields:
            firstName: ~
            lastName: ~
            email: ~
            addresses:
                postalCode: ~
                phone: ~
                country: ~
        strategy:
            addresses: any_of
        options:
            match: first
            empty: false
```

### Address Strategy

Strategy allows to manage how relations detected.

Next strategies available:

* any_of 

imported address checked on any matched entity address;

* by_type

imported address checked on matched entity address with same type;

* shipping

imported address checked on matched entity address with shipping type;

* billing

imported address checked on matched entity address with billing type;


### Options

* match: (first|latest)

In case of multiple matched entities this option defines what entity to chose - first or last.

* empty: false

Defines behaviour for empty values. Comparison of target field value and empty matched field value acts as matched if 
option value is true.
