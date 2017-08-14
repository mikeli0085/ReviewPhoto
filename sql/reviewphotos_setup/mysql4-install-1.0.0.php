<?php
/**
 * Review Photos for Magento 1.6
 * Designed by Fast Division (http://fastdivision.com)
 *
 * @author     Fast Division
 * @version    1.0.0
 * @copyright  Copyright 2011 Fast Division
 * @license    All rights reserved.
 */

$installer = $this;
$installer->startSetup();

Mage::helper('reviewphotos/install')->addColumns($installer, $this->getTable('review/review_detail'),
        array("`photo_url` VARCHAR(255)", "`photo_title` VARCHAR(255)", "`photo_description` TEXT", "`related_product_ids` VARCHAR(255)", "`photo_url2` VARCHAR(255)", "`photo_url3` VARCHAR(255)", "`photo_url4` VARCHAR(255)", "`photo_url5` VARCHAR(255)", "`photo_url6` VARCHAR(255)"));
        
$msg_title = "Review Photos was successfully installed on your store!";
$msg_desc = "Enjoy and let us know if you have any questions or feedback.";
Mage::Helper('reviewphotos/install')->createInstallNotice($msg_title, $msg_desc);

$installer->endSetup();