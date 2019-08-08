<?php

namespace DNADesign\HTTPCacheControl;

use SilverStripe\Core\Extension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;

/**
 * This extension uses the field data "max-age" and sets the max age.
 * This will be applied to the Controller::init function for the controller
 * you add this extension to.
 */
class ControllerExtension extends Extension
{

    /**
     * @var int $cacheAge Max-age seconds to cache for.
     */
    private static $cacheAge_default = 120;

    /**
     * Called by ContentController::init();
     */
    public function onBeforeInit()
    {
        $cacheControl = HTTPCacheControlMiddleware::singleton();
        $response = $this->owner->getResponse();
        $request = $this->owner->getRequest();

        // Leverage the logic from  HTTPCacheControlMiddleware::augmentState
        // here so we dont overide what will be set in augmentState.

        // Errors disable cache (unless some errors are cached intentionally by usercode)
        if ($response->isError() || $response->isRedirect()) {
            // Even if publicCache(true) is specified, errors will be uncacheable
            $cacheControl->disableCache(true);
            return;
        } elseif ($request->getSession()->getAll()) {
            // If sessions exist we assume that the responses should not be cached by CDNs / proxies as we are
            // likely to be supplying information relevant to the current user only

            // Don't force in case user code chooses to opt in to public caching
            $cacheControl->privateCache();
            return;
        }

        if ($this->getDisableCache()) {
            $cacheControl->disableCache($force = true);
        } else {
            $cacheControl
                ->enableCache($this->getForceCache())
                ->setMaxAge($this->getCacheAge())
                ->publicCache($force = true);
        }
    }

    public function getDisableCache()
    {
        if ($this->owner->failover->Config()->get('http_cache_disable')) {
            return true;
        }

        if ($this->owner->failover->MaxAge == '0') {
            return true;
        }
    }

    public function getForceCache()
    {
        if ($this->owner->failover->Config()->get('http_cache_force')) {
            return true;
        }
    }

    public function getCacheAge()
    {
        /* http_cache_disable can be used on subclasses to override the behaviour */
        if ($this->owner->failover->Config()->get('http_cache_disable')) {
            return 0;
        }

        //any page with forms in its elements shouldnt be cached
        if ($this->owner->failover->hasExtension('DNADesign\Elemental\Extensions\ElementalPageExtension')) {
            $area = $this->owner->ElementalArea();
            $elements = $area->Elements();

            foreach ($elements as $element) {
                /* http_cache_disable can be used on Elements to override the behaviour
                    this is particularly useful for elements containing forms which
                    contain a security ID specific to the user

                    Another way to approach this and still get decent caching on the page
                    is to lazy load those elements after the initial page request
                */
                if ($element->Config()->get('http_cache_disable')) {
                    return 0;
                } elseif ($element->ClassName == 'DNADesign\ElementalVirtual\Model\ElementVirtual') {
                    if ($element->LinkedElement()->exists() && $element->LinkedElement()->Config()->get('http_cache_disable')) {
                        return 0;
                    }
                }
            }
        }

        if ($this->owner->failover->MaxAge != '' && $this->owner->failover->MaxAge != '0') {
            return (int) ($this->owner->failover->MaxAge * 60);
        }
        return (int) Config::inst()->get('DNADesign\HTTPCacheControl\ControllerExtension', 'cacheAge_default');
    }
}
