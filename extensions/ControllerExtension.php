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
    public  function onBeforeInit()
    {
        $cacheControl = HTTPCacheControlMiddleware::singleton();

        if ($this->getDisableCache()) {
            $cacheControl->disableCache($force = true);
        } else {
            $cacheControl
                ->enableCache($this->getForceCache())
                ->setMaxAge($this->getCacheAge());

            $this->getPublicCache($cacheControl);
        }
    }

    public function getDisableCache()
    {
        if ($this->owner->failover->Config()->get('http_cache_disable')) return true;
    }

    public function getForceCache()
    {
        if ($this->owner->failover->Config()->get('http_cache_force')) return true;
    }

    public function getPublicCache($cacheControl)
    {
        if ($this->owner->failover->Config()->get('http_cache_public')) {
            $cacheControl->publicCache($force = true);
        }
    }

    public function getCacheAge()
    {
        /* http_cache_disable can be used on subclasses to override the behaviour */
        if ($this->owner->failover->Config()->get('http_cache_disable')) return 0;

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
                } else if ($element->ClassName == 'DNADesign\ElementalVirtual\Model\ElementVirtual') {
                    if ($element->LinkedElement()->exists() && $element->LinkedElement()->Config()->get('http_cache_disable')) {
                        return 0;
                    }
                }
            }
        }

        if ($this->owner->MaxAge != '') {
            return (int) ($this->owner->MaxAge * 60);
        }
        return (int) Config::inst()->get('DNADesign\HTTPCacheControl\ControllerExtension', 'cacheAge_default');
    }
}
