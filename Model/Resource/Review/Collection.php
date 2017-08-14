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
 
class FastDivision_ReviewPhotos_Model_Resource_Review_Collection extends Mage_Review_Model_Resource_Review_Collection
{
    protected function _initSelect()
    {
        $this->getSelect()->from(array('main_table' => $this->getMainTable()));
        $this->getSelect()
            ->join(array('detail' => $this->_reviewDetailTable),
                'main_table.review_id = detail.review_id',
                array('detail_id', 'title', 'detail', 'nickname', 'customer_id', 'photo_url', 'photo_title', 'photo_description','related_product_ids','photo_url2','photo_url3','photo_url4','photo_url5','photo_url6'));
        return $this;
    }
}