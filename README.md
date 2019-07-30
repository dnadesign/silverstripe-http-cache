# Silverstripe HTTP Cache

Enables caching for all page content (through PageController).

## Enabling cache

In your extensions yml file include

```yml
Page:
  http_cache_public: false
  http_cache_force: false
  extensions:
    - PageExtension
PageController:
  extensions:
    - ControllerExtension
```

Note that you can set your class with public caching and also force cache in it, when necessary.

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

## Testing

You can test your header directly in your browser's devTools or in your terminal with the following command

```sh
curl --silent --dump-header - --write-out 'Total (secs): %{time_total}' http://yoursite.com --output /dev/null
```

## References
For more info refer to [Silverstripe HTTP Cache Headers docs](https://docs.silverstripe.org/en/4/developer_guides/performance/http_cache_headers/)
