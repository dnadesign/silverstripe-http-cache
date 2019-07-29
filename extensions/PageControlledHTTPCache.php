<?php

use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldList;

/**
 * This extension adds the ability to control the max-age per originator.
 * The configuration option is surfaced to the CMS UI. The extension needs to be added
 * to the object related to the policed controller.
 */
class PageControlledHTTPCache extends DataExtension
{

    /**
     * @var array
     */
    private static $db = array(
        'MaxAge' => 'Varchar'
    );

    /**
     * @param  FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        // Only admins are allowed to modify this.
        $member = Member::currentUser();
        if (!$member || !Permission::checkMember($member, 'ADMIN')) {
            return;
        }

        /* http_cache_disable can be used on subclasses to override the behaviour */
        if ($this->owner->Config()->get('http_cache_disable')) return;

        $default = Config::inst()->get('PageControlledHTTPCacheController', 'cacheAge_default');

        $meta = $fields->fieldByName('Root.Main.Metadata');
        $meta->push(
            $ma = new TextField('MaxAge', 'Custom cache timeout [minutes]')
        );
        $ma->setRightTitle('This field controls the length of time the page will be cached for.<br/>' .
            'You will not be able to see updates to this page for at most the specified amount of minutes.<br/>' .
            'Leave empty to set back to the default which is <strong>' . ($default / 60) . ' mins</strong>. ' .
            'Set to 0 to explicitly disable caching for this page. <br/>Also if this page contains elements ' .
            'which have forms, the caching will be disabled automatically.');
    }
}
