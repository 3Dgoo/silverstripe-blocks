<?php

namespace SheaDawson\Blocks\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

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
        if (
            $this->getOwner()->data()->canEdit()
            && $this->getOwner()->getRequest()->getVar('block_preview') == 1
        ) {
            Requirements::javascript('https://code.jquery.com/jquery-3.7.1.min.js');
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
        $block = $this->getOwner()->data()
            ->getBlockList(null, true, true, true)
            ->find('ID', $this->getOwner()->getRequest()->param('ID') ?? 0);
        
        if (!$block) {
            return;
        }

        return $block->getControllerName();
    }
}
