<?php

namespace DNADesign\HTTPCacheControl;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;

/**
 * This extension adds the ability to control the max-age per originator.
 * The configuration option is surfaced to the CMS UI. The extension needs to be added
 * to the object related to the policed controller.
 */
class PageExtension extends DataExtension
{

    /**
     * @var array
     */
    private static $db = array(
        'MaxAge' => 'Int'
    );

    /**
     * @param  FieldList $fields
     */
    public function updateSettingsFields(FieldList $fields)
    {
        // Only admins are allowed to modify this.
        $member = Member::currentUser();
        if (!$member || !Permission::checkMember($member, 'ADMIN')) {
            return;
        }

        $default = Config::inst()->get('DNADesign\HTTPCacheControl\ControllerExtension', 'cacheAge_default');

        /* http_cache_disable can be used on subclasses to override the behaviour */
        if ($this->owner->Config()->get('http_cache_disable')) {
            $maxAge = NumericField::create('MaxAgeOverriden', 'Custom cache timeout [minutes]', 0);
            $maxAge->setDisabled(true);

            $maxAge->setDescription('This field controls the length of time the page will be cached for.<br/>' .
                'Overriden in the site configuration');
        } else {
            $maxAge = NumericField::create('MaxAge', 'Custom cache timeout [minutes]');
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
