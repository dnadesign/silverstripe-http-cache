<?php

namespace DNADesign\HTTPCacheControl;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;

/**
 * This extension adds the ability to control the max-age per originator.
 * The configuration option is surfaced to the CMS UI. The extension needs to be added
 * to the object related to the policed controller.
 *
 * @property ?string $MaxAge
 * @extends Extension<DataObject>
 */
class PageExtension extends Extension
{
    private static array $db = [
        'MaxAge' => 'Varchar(10)',
    ];

    public function updateSettingsFields(FieldList $fields): void
    {
        // Only admins are allowed to modify this.
        $member = Security::getCurrentUser();
        if (!$member || !Permission::checkMember($member, 'ADMIN')) {
            return;
        }

        $default = Config::inst()->get(ControllerExtension::class, 'cacheAge_default');

        /* http_cache_disable can be used on subclasses to override the behaviour */
        if ($this->getOwner()->Config()->get('http_cache_disable')) {
            $maxAge = TextField::create('MaxAgeOverriden', 'Custom cache timeout [minutes]', 0);
            $maxAge->setDisabled(true);

            $maxAge->setDescription('This field controls the length of time the page will be cached for.<br/>' .
                'Overriden in the site configuration');
        } else {
            $maxAge = TextField::create('MaxAge', 'Custom cache timeout [minutes]');
            $maxAge->setDescription('This field controls the length of time the page will be cached for.<br/>' .
                'You will not be able to see updates to this page for at most the specified amount of minutes.<br/>' .
                'Leave empty to set back to the default which is <strong>' . ($default / 60) . ' mins</strong>. ' .
                'Set to 0 to explicitly disable caching for this page. <br/>Also if this page contains elements ' .
                'which have forms, the caching will be disabled automatically.');
        }

        $fields->addFieldToTab(
            'Root.Cache',
            $maxAge
        );
    }
}
