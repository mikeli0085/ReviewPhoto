<?php

/**
 * Member Photos for Magento 1.6
 * Designed by Fast Division (http://fastdivision.com)
 *
 * @author     Fast Division
 * @version    1.0.0
 * @copyright  Copyright 2011 Fast Division
 * @license    All rights reserved.
 */
class FastDivision_ReviewPhotos_Block_Manage_Review_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('review_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('review')->__('Review Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('review')->__('Review Details'),
            'title' => Mage::helper('review')->__('Review Details'),
            'content' => $this->getLayout()->createBlock('reviewphotos/manage_review_edit_tab_form')->toHtml(),
        ));

        $this->addTab('products', array(
            'label' => Mage::helper('review')->__('Related Products'),
            'url' => $this->getUrl('*/*/products', array('_current' => true)),
            'class' => 'ajax',
        ));

        return parent::_beforeToHtml();
    }

}
