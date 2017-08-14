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
class FastDivision_ReviewPhotos_Block_Manage_Review_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'reviewphotos';
        $this->_controller = 'manage_review';

        $this->_updateButton('save', 'label', Mage::helper('review')->__('Save Review'));
        $this->_updateButton('save', 'id', 'save_button');
        $this->_updateButton('delete', 'label', Mage::helper('review')->__('Delete Review'));

        if( $this->getRequest()->getParam('productId', false) ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/catalog_product/edit', array('id' => $this->getRequest()->getParam('productId', false))) .'\')' );
        }

        if( $this->getRequest()->getParam('customerId', false) ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/customer/edit', array('id' => $this->getRequest()->getParam('customerId', false))) .'\')' );
        }

        if( $this->getRequest()->getParam('ret', false) == 'pending' ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/*/pending') .'\')' );
            $this->_updateButton('delete', 'onclick', 'deleteConfirm(\'' . Mage::helper('review')->__('Are you sure you want to do this?') . '\', \'' . $this->getUrl('*/*/delete', array(
                $this->_objectId => $this->getRequest()->getParam($this->_objectId),
                'ret'           => 'pending',
            )) .'\')' );
            Mage::register('ret', 'pending');
        }

        if( $this->getRequest()->getParam($this->_objectId) ) {
            $reviewData = Mage::getModel('review/review')
                ->load($this->getRequest()->getParam($this->_objectId));
            //Mage::register('review_data', $reviewData);
        }

        $this->_formInitScripts[] = '
            var review = {
                updateRating: function() {
                        elements = [$("select_stores"), $("rating_detail").getElementsBySelector("input[type=\'radio\']")].flatten();
                        $(\'save_button\').disabled = true;
                        new Ajax.Updater("rating_detail", "'.$this->getUrl('*/*/ratingItems', array('_current'=>true)).'", {parameters:Form.serializeElements(elements), evalScripts:true, onComplete:function(){ $(\'save_button\').disabled = false; } });
                    }
           }
           Event.observe(window, \'load\', function(){
                 Event.observe($("select_stores"), \'change\', review.updateRating);
           });
        ';
    }

    public function getHeaderText() {
        if( Mage::registry('review_data') && Mage::registry('review_data')->getId() ) {
            return Mage::helper('review')->__("Edit Review '%s'", $this->htmlEscape(Mage::registry('review_data')->getTitle()));
        } else {
            return Mage::helper('review')->__('New Review');
        }
    }

}
