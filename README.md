# Silverstripe HTTP Cache

Enables caching for all page content (through PageController).

## Enabling cache

In your extensions yml file include

```yml
Page:
  extensions:
    - PageControlledHTTPCache
PageController:
  extensions:
    - PageControlledHTTPCacheController
```
