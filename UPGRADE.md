## FROM 2.5.0 to 2.6.0
* Changed minimum required php version to 7.1

## FROM 2.1.0 to 2.2.0

* Search index fields `description`, `resolution` and `message` for `CaseEntity` now contain no more than **255** characters each.
    * Please, run re-indexation for this entity using command:
        ```bash
          php app/console oro:search:reindex OroCaseBundle:CaseEntity --env=prod
        ```

## FROM 2.0.0 to 2.1.0

* Changed minimum required php version to 7.0
* Updated dependency to [fxpio/composer-asset-plugin](https://github.com/fxpio/composer-asset-plugin) composer plugin to version 1.3.
* Composer updated to version 1.4.

```
    composer self-update
    composer global require "fxp/composer-asset-plugin"
```