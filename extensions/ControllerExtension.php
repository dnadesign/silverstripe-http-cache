<?php

namespace DNADesign\HTTPCacheControl;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;

/**
 * This extension uses the field data "max-age" and sets the max age.
 * This will be applied to the Controller::init function for the controller
 * you add this extension to.
 *
 * @extends Extension<Controller>
 */
class ControllerExtension extends Extension
{
    /**
     * Max-age seconds to cache for.
     */
    private static int $cacheAge_default = 120;

    /**
     * Called by ContentController::init();
     */
    public function onBeforeInit(): void
    {
        $cacheControl = HTTPCacheControlMiddleware::singleton();

        if ($this->getDisableCache()) {
            $cacheControl->disableCache(true);
        } else {
            $cacheControl
                ->enableCache($this->getForceCache())
                ->setMaxAge($this->getCacheAge())
                ->publicCache();
        }
    }

    public function getDisableCache(): bool
    {
        if ($this->getOwner()->Config()->get('http_cache_disable')) {
            return true;
        }

        if ($this->getOwner()->getFailover()->Config()->get('http_cache_disable')) {
            return true;
        }

        if ($this->getOwner()->getFailover()->MaxAge == '0') {
            return true;
        }

        return false;
    }

    public function getForceCache(): bool
    {
        return $this->getOwner()->getFailover()->Config()->get('http_cache_force') === true;
    }

    public function getCacheAge(): int
    {
        if ($this->getOwner()->Config()->get('http_cache_disable')) {
            return 0;
        }

        /* http_cache_disable can be used on subclasses to override the behaviour */
        if ($this->getOwner()->getFailover()->Config()->get('http_cache_disable')) {
            return 0;
        }

        //any page with forms in its elements shouldnt be cached
        if ($this->getOwner()->getFailover()->hasExtension('DNADesign\Elemental\Extensions\ElementalPageExtension')) {
            $area = $this->getOwner()->ElementalArea(); // @phpstan-ignore-line method.notFound
            $elements = $area->Elements();

            foreach ($elements as $element) {
                // http_cache_disable can be used on Elements to override the behaviour
                // this is particularly useful for elements containing forms which
                // contain a security ID specific to the user

                // Another way to approach this and still get decent caching on the page
                // is to lazy load those elements after the initial page request

                if ($element->Config()->get('http_cache_disable')) {
                    return 0;
                } elseif ($element->ClassName == 'DNADesign\ElementalVirtual\Model\ElementVirtual') {
                    if ($element->LinkedElement()->exists() && $element->LinkedElement()->Config()->get('http_cache_disable')) {
                        return 0;
                    }
                }
            }
        }

        if ($this->getOwner()->getFailover()->MaxAge != '' && $this->getOwner()->getFailover()->MaxAge != '0') {
            return (int) ($this->getOwner()->getFailover()->MaxAge * 60);
        }
        return (int) Config::inst()->get(self::class, 'cacheAge_default');
    }
}
