<?php

namespace SheaDawson\Blocks\Extensions;

use SilverStripe\View\Requirements;
use SilverStripe\Core\Extension;

class BlocksContentControllerExtension extends Extension
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'handleBlock',
    ];

    public function onAfterInit()
    {
        if ($this->owner->data()->canEdit() && $this->owner->getRequest()->getVar('block_preview') == 1) {            Requirements::javascript('https://code.jquery.com/jquery-3.7.1.min.js');
            Requirements::javascript('sheadawson/silverstripe-blocks: javascript/block-preview.js');
            Requirements::css('sheadawson/silverstripe-blocks: css/block-preview.css');
        }
    }

    /**
     * Handles blocks attached to a page
     * Assumes URLs in the following format: <URLSegment>/block/<block-ID>.
     *
     * @return RequestHandler
     */
    public function handleBlock()
    {
        $block = $this->owner->data()
            ->getBlockList(null, true, true, true)
            ->find('ID', $this->owner->getRequest()->param('ID') ?? 0);
        
        if (!$block) {
            return;
        }

        return $block->getControllerName();
    }
}
