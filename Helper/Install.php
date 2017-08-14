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
 
class FastDivision_ReviewPhotos_Helper_Install extends Mage_Core_Helper_Abstract {
    
    public function addColumns(&$installer, $table_name, $columns) {
		foreach ($columns as $column) {
			$sql = "ALTER TABLE {$table_name} ADD COLUMN ({$column});";
			try {
				$installer->run($sql);
			} catch(Exception $ex) {}
		}
		
		return $this;
	}
	
	public function createInstallNotice($msg_title, $msg_desc, $url = null) {
		$message = Mage::getModel('adminnotification/inbox');
		$message->setDateAdded(date("c", time()));
		
		if($url == null) {
		  $url = "http://fastdivision.com/extensions/review-photos";
		}
		
		$message->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
		$message->setTitle($msg_title);
		$message->setDescription($msg_desc);
		$message->setUrl($url);
		$message->save();
		
		return $this;
	}
}
