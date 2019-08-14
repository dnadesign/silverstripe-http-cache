# Silverstripe HTTP Cache

Enables caching for all page content (through PageController).

## Enabling cache

In your extensions yml file include

```yml
#Enable Caching on dev mode
---
Name: 'app_httpconfig'
After: '#httpconfig-dev'
Only:
  environment: dev
---
SilverStripe\Control\Middleware\HTTPCacheControlMiddleware:
  defaultState: 'disabled'
  defaultForcingLevel: 0

#Module config
---
Name: appcache
After: '#cwpcoreconfig'
---
Page:
  http_cache_force: false
  extensions:
    - PageExtension
PageController:
  extensions:
    - ControllerExtension
```

**USE AT YOUR OWN RISK**
You can force caching by setting `http_cache_force` to true, that will force cache all over the website IGNORING any default SS behaviours.

### Disabling cache

You can disable cache on a page or pages with specific elements as below.

**Page** will have cache disabled

```yml
namespace\for\Page:
  http_cache_disable: true
```

Any page with **Element** will have cache disabled

```yml
namespace\for\Element:
  http_cache_disable: true
```

## CMS

![CMS Settings Field][cms]

[cms]: https://i.imgur.com/5fnZ8fp.png "CMS Field"

## Testing

You can test your header directly in your browser's devTools or in your terminal with the following command

```sh
curl --silent --dump-header - --write-out 'Total (secs): %{time_total}' http://yoursite.com --output /dev/null
```

## References
For more info refer to [Silverstripe HTTP Cache Headers docs](https://docs.silverstripe.org/en/4/developer_guides/performance/http_cache_headers/)
