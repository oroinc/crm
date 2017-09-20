## General

  * Pull changes from repository
```bash
git pull
git checkout <VERSION TO UPGRADE>
```
  * Upgrade composer dependency
```bash
php composer.phar install --prefer-dist
```
  * Remove old caches and assets
```bash
rm -rf app/cache/*
rm -rf web/js/*
rm -rf web/css/*
```
  * Upgrade platform
```bash
php app/console oro:platform:update --env=prod --force
```

## FROM 2.2.0 to 2.3.0

* The class `Oro\Bundle\SecurityBundle\SecurityFacade`, services `oro_security.security_facade` and `oro_security.security_facade.link`, and TWIG function `resource_granted` were marked as deprecated.
* Use services `security.authorization_checker`, `security.token_storage`, `oro_security.token_accessor`, `oro_security.class_authorization_checker`, `oro_security.request_authorization_checker` and TWIG function `is_granted` instead.
* In controllers use `isGranted` method from `Symfony\Bundle\FrameworkBundle\Controller\Controller`.
* The usage of deprecated service `security.context` (interface `Symfony\Component\Security\Core\SecurityContextInterface`) was removed as well.
* All existing classes were updated to use new services instead of the `SecurityFacade` and `SecurityContext`:
    * service `security.authorization_checker`
        * implements `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
        * the property name in classes that use this service is `authorizationChecker`
    * service `security.token_storage`
        * implements `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
        * the property name in classes that use this service is `tokenStorage`
    * service `oro_security.token_accessor`
        * implements `Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface`
        * the property name in classes that use this service is `tokenAccessor`
    * service `oro_security.class_authorization_checker`
        * implements `Oro\Bundle\SecurityBundle\Authorization\ClassAuthorizationChecker`
        * the property name in classes that use this service is `classAuthorizationChecker`
    * service `oro_security.request_authorization_checker`
        * implements `Oro\Bundle\SecurityBundle\Authorization\RequestAuthorizationChecker`
        * the property name in classes that use this service is `requestAuthorizationChecker`

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