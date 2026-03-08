<?php

namespace SheaDawson\Blocks\Forms;

use SheaDawson\Blocks\BlockManager;
use SheaDawson\Blocks\Model\Block;
use SheaDawson\Blocks\Model\BlockSet;
use SilverStripe\CMS\Controllers\CMSPageEditController;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use Symbiote\GridFieldExtensions\GridFieldAddExistingSearchButton;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

/**
 * GridFieldConfig_BlockManager
 * Provides a reusable GridFieldConfig for managing Blocks.
 *
 * @author Shea Dawson <shea@livesource.co.nz>
 */
class GridFieldConfigBlockManager extends GridFieldConfig
{
    public $blockManager;

    public function __construct($canAdd = true, $canEdit = true, $canDelete = true, $editableRows = false, $aboveOrBelow = false)
    {
        parent::__construct();

        $this->blockManager = Injector::inst()->get(BlockManager::class);
        $controllerClass = get_class(Controller::curr());
        // Get available Areas (for page) or all in case of ModelAdmin
        if ($controllerClass == CMSPageEditController::class) {
            $currentPage = Controller::curr()->currentPage();
            $areasFieldSource = $this->blockManager->getAreasForPageType($currentPage->ClassName);
        } else {
            $areasFieldSource = $this->blockManager->getAreas();
        }

        // EditableColumns only makes sense on Saveable parenst (eg Page), or inline changes won't be saved
        if ($editableRows) {
            $this->addComponent($editable = GridFieldEditableColumns::create());
            $displayfields = array(
                'TypeForGridfield' => array('title' => _t('Block.BlockType', 'Block Type'), 'field' => 'SilverStripe\\Forms\\LiteralField'),
                'Title' => array('title' => _t('Block.Title', 'Title'), 'field' => 'Silverstripe\\Forms\\ReadonlyField'),
                'BlockArea' => array(
                    'title' => _t('Block.BlockArea', 'Block Area'),
                    'callback' => function () use ($areasFieldSource) {
                            $areasField = DropdownField::create('BlockArea', 'Block Area', $areasFieldSource);
                            if (count($areasFieldSource) > 1) {
                                $areasField->setHasEmptyDefault(true);
                            }
                            return $areasField;
                        },
                ),
                'isPublishedIcon' => array('title' => _t('Block.IsPublishedField', 'Published'), 'field' => 'SilverStripe\\Forms\\LiteralField'),
                'UsageListAsString' => array('title' => _t('Block.UsageListAsString', 'Used on'), 'field' => 'SilverStripe\\Forms\\LiteralField'),
            );

            if ($aboveOrBelow) {
                $displayfields['AboveOrBelow'] = array(
                    'title' => _t('GridFieldConfigBlockManager.AboveOrBelow', 'Above or Below'),
                    'callback' => function () {
                        return DropdownField::create('AboveOrBelow', _t('GridFieldConfigBlockManager.AboveOrBelow', 'Above or Below'), BlockSet::config()->get('above_or_below_options'));
                    },
                );
            }
            $editable->setDisplayFields($displayfields);
        } else {
            $this->addComponent($dcols = GridFieldDataColumns::create());

            $displayfields = array(
                'TypeForGridfield' => array('title' => _t('Block.BlockType', 'Block Type'), 'field' => 'SilverStripe\\Forms\\LiteralField'),
                'Title' => _t('Block.Title', 'Title'),
                'BlockArea' => _t('Block.BlockArea', 'Block Area'),
                'isPublishedIcon' => array('title' => _t('Block.IsPublishedField', 'Published'), 'field' => 'SilverStripe\\Forms\\LiteralField'),
                'UsageListAsString' => _t('Block.UsageListAsString', 'Used on'),
            );
            $dcols->setDisplayFields($displayfields);
            $dcols->setFieldCasting(array('UsageListAsString' => 'HTMLText->Raw'));
        }


        $this->addComponent(GridFieldButtonRow::create('before'));
        $this->addComponent(GridFieldToolbarHeader::create());
        $this->addComponent(GridFieldDetailForm::create());
        $this->addComponent($sort = GridFieldSortableHeader::create());
        $this->addComponent($filter = GridFieldFilterHeader::create());
        $this->addComponent(GridFieldDetailForm::create());

        if ($canAdd) {
            $multiClass = GridFieldAddNewMultiClass::create();
            $classes = $this->blockManager->getBlockClasses();
            $multiClass->setClasses($classes);
            $this->addComponent($multiClass);
            //$this->addComponent(new GridFieldAddNewButton());
        }

        if ($canEdit) {
            $this->addComponent(GridFieldEditButton::create());
        }

        if ($canDelete) {
            $this->addComponent(GridFieldDeleteAction::create(true));
        }

        return $this;
    }

    /**
     * Add the GridFieldAddExistingSearchButton component to this grid config.
     *
     * @return $this
     **/
    public function addExisting()
    {
        $classes = $this->blockManager->getBlockClasses();

        $this->addComponent($add = GridFieldAddExistingSearchButton::create());
        $add->setSearchList(Block::get()->filter(array(
            'ClassName' => array_keys($classes),
        )));

        return $this;
    }
}