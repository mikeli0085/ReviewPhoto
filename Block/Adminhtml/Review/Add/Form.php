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
 
class FastDivision_ReviewPhotos_Block_Adminhtml_Review_Add_Form extends Mage_Adminhtml_Block_Review_Add_Form
{
    protected function _prepareForm()
    { 
      $statuses = Mage::getModel('review/review')
          ->getStatusCollection()
          ->load()
          ->toOptionArray();

      $form = new Varien_Data_Form(array(
          'enctype' => 'multipart/form-data'
      ));

      $fieldset = $form->addFieldset('add_review_form', array('legend' => Mage::helper('review')->__('Review Details')));

      $fieldset->addField('product_name', 'note', array(
          'label'     => Mage::helper('review')->__('Product'),
          'text'      => 'product_name',
      ));

      $fieldset->addField('detailed_rating', 'note', array(
          'label'     => Mage::helper('review')->__('Product Rating'),
          'required'  => true,
          'text'      => '<div id="rating_detail">' . $this->getLayout()->createBlock('adminhtml/review_rating_detailed')->toHtml() . '</div>',
      ));

      $fieldset->addField('status_id', 'select', array(
          'label'     => Mage::helper('review')->__('Status'),
          'required'  => true,
          'name'      => 'status_id',
          'values'    => $statuses,
      ));

      /**
       * Check is single store mode
       */
      if (!Mage::app()->isSingleStoreMode()) {
          $fieldset->addField('select_stores', 'multiselect', array(
              'label'     => Mage::helper('review')->__('Visible In'),
              'required'  => true,
              'name'      => 'select_stores[]',
              'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
          ));
      }
      
      // $tagfieldset = $form->addFieldset('tag_review_products', array('legend' => Mage::helper('review')->__('Related Products'), 'class' => 'fieldset-wide'));
      // $tagfieldset->addField('related_product_ids', 'text', array(
      //     'label'     => Mage::helper('review')->__('Product Ids'),
      //     'required'  => false,
      //     'name'      => 'related_product_ids',
      //     'values'    => Mage::helper('review')->translateArray($statuses),
      // ));
      
      // Review Photo
      $mediaFieldset = $form->addFieldset('review_media', array('legend' => Mage::helper('review')->__('Review Media'), 'class' => 'fieldset-wide'));

      $mediaFieldset->addType('review_image', 'FastDivision_ReviewPhotos_Lib_Varien_Data_Form_Element_ReviewImage');
      $mediaFieldset->addField('photo_url', 'review_image', array(
          'label'         => 'Review Photos',
          'name'          => 'photo',
          'required'      => false
      ));
      $mediaFieldset->addType('review_image', 'FastDivision_ReviewPhotos_Lib_Varien_Data_Form_Element_ReviewImage');
      $mediaFieldset->addField('photo_url2', 'review_image', array(
          'label'         => '',
          'name'          => 'photo2',
          'required'      => false
      ));
      $mediaFieldset->addType('review_image', 'FastDivision_ReviewPhotos_Lib_Varien_Data_Form_Element_ReviewImage');
      $mediaFieldset->addField('photo_url3', 'review_image', array(
          'label'         => '',
          'name'          => 'photo3',
          'required'      => false
      ));
      $mediaFieldset->addType('review_image', 'FastDivision_ReviewPhotos_Lib_Varien_Data_Form_Element_ReviewImage');
      $mediaFieldset->addField('photo_url4', 'review_image', array(
          'label'         => '',
          'name'          => 'photo4',
          'required'      => false
      ));
      $mediaFieldset->addType('review_image', 'FastDivision_ReviewPhotos_Lib_Varien_Data_Form_Element_ReviewImage');
      $mediaFieldset->addField('photo_url5', 'review_image', array(
          'label'         => '',
          'name'          => 'photo5',
          'required'      => false
      ));
      $mediaFieldset->addType('review_image', 'FastDivision_ReviewPhotos_Lib_Varien_Data_Form_Element_ReviewImage');
      $mediaFieldset->addField('photo_url6', 'review_image', array(
          'label'         => '',
          'name'          => 'photo6',
          'required'      => false
      ));
      
      if(Mage::getStoreConfigFlag('reviewphotos_config/reviewphotos_general/reviewphotos_show_title')) {
          $mediaFieldset->addField('photo_title', 'text', array(
              'label'         => 'Photo Title',
              'name'          => 'photo_title',
              'required'      => false
          ));
      }
      
      if(Mage::getStoreConfigFlag('reviewphotos_config/reviewphotos_general/reviewphotos_show_description')) {
          $mediaFieldset->addField('photo_description', 'textarea', array(
              'label'         => 'Photo Description',
              'name'          => 'photo_description',
              'required'      => false,
              'style'        => 'height: 6em;'
          ));
      }
      
      $fieldset->addField('nickname', 'text', array(
          'name'      => 'nickname',
          'title'     => Mage::helper('review')->__('Nickname'),
          'label'     => Mage::helper('review')->__('Nickname'),
          'maxlength' => '50',
          'required'  => true,
      ));

      $fieldset->addField('title', 'text', array(
          'name'      => 'title',
          'title'     => Mage::helper('review')->__('Summary of Review'),
          'label'     => Mage::helper('review')->__('Summary of Review'),
          'maxlength' => '255',
          'required'  => true,
      ));

      $fieldset->addField('detail', 'textarea', array(
          'name'      => 'detail',
          'title'     => Mage::helper('review')->__('Review'),
          'label'     => Mage::helper('review')->__('Review'),
          'style'     => 'width: 98%; height: 600px;',
          'required'  => true,
      ));

      $fieldset->addField('product_id', 'hidden', array(
          'name'      => 'product_id',
      ));

      /*$gridFieldset = $form->addFieldset('add_review_grid', array('legend' => Mage::helper('review')->__('Please select a product')));
      $gridFieldset->addField('products_grid', 'note', array(
          'text' => $this->getLayout()->createBlock('adminhtml/review_product_grid')->toHtml(),
      ));*/

      $form->setMethod('post');
      $form->setUseContainer(true);
      $form->setId('edit_form');
      $form->setAction($this->getUrl('*/*/post'));
      $this->setForm($form);
    }
}