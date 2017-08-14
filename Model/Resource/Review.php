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
 
class FastDivision_ReviewPhotos_Model_Resource_Review extends Mage_Review_Model_Resource_Review
{
    /**
     * Perform actions after object save
     *
     * @param Varien_Object $object
     * @return Mage_Review_Model_Resource_Review
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getWriteAdapter();
        /**
         * save detail
         */
        $detail = array(
            'title'     => $object->getTitle(),
            'detail'    => $object->getDetail(),
            'nickname'  => $object->getNickname(),
            'photo_url' => $object->getPhotoUrl(),
            'photo_title' => $object->getPhotoTitle(),
            'photo_description' => $object->getPhotoDescription(),
            'related_product_ids' => $object->getRelatedProductIds(),
            'photo_url2' => $object->getPhotoUrl2(),
            'photo_url3' => $object->getPhotoUrl3(),
            'photo_url4' => $object->getPhotoUrl4(),
            'photo_url5' => $object->getPhotoUrl5(),
            'photo_url6' => $object->getPhotoUrl6(),
        );
        $select = $adapter->select()
            ->from($this->_reviewDetailTable, 'detail_id')
            ->where('review_id = :review_id');
        $detailId = $adapter->fetchOne($select, array(':review_id' => $object->getId()));

        if ($detailId) {
            $condition = array("detail_id = ?" => $detailId);
            $adapter->update($this->_reviewDetailTable, $detail, $condition);
        } else {
            $detail['store_id']   = $object->getStoreId();
            $detail['customer_id']= $object->getCustomerId();
            $detail['review_id']  = $object->getId();
            $adapter->insert($this->_reviewDetailTable, $detail);
        }


        /**
         * save stores
         */
        $stores = $object->getStores();
        if (!empty($stores)) {
            $condition = array('review_id = ?' => $object->getId());
            $adapter->delete($this->_reviewStoreTable, $condition);

            $insertedStoreIds = array();
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = array(
                    'store_id' => $storeId,
                    'review_id'=> $object->getId()
                );
                $adapter->insert($this->_reviewStoreTable, $storeInsert);
            }
        }

        // reaggregate ratings, that depend on this review
        $this->_aggregateRatings(
            $this->_loadVotedRatingIds($object->getId()),
            $object->getEntityPkValue()
        );

        return $this;
    }

    /**
     * Retrieves total reviews
     *
     * @param int $entityPkValue
     * @param bool $approvedOnly
     * @param int $storeId
     * @return int
     */
    public function getTotalReviews($entityPkValue, $approvedOnly = false, $storeId = 0)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_reviewTable,
                array(
                    'review_count' => new Zend_Db_Expr('COUNT(*)')
                ))
            ->where("{$this->_reviewTable}.entity_pk_value = :pk_value");        
        $bind = array(':pk_value' => $entityPkValue);
        if ($storeId > 0) {
            $select->join(array('store'=>$this->_reviewStoreTable),
                $this->_reviewTable.'.review_id=store.review_id AND store.store_id = :store_id',
                array());
            $bind[':store_id'] = (int)$storeId;
        }
        if ($approvedOnly) {
            $select->where("{$this->_reviewTable}.status_id = :status_id");
            $bind[':status_id'] = 1;
        }
        $reviews_count = $adapter->fetchOne($select, $bind);
        $select = $adapter->select('review_id')
             ->from($this->_reviewTable, array('review_id','review_id'))
             ->where("{$this->_reviewTable}.entity_pk_value = :pk_value");        
        $bind = array(':pk_value' => $entityPkValue);
        $result = $adapter->fetchAll($select, $bind);
        $revewids = array();
        foreach ($result as $review) {
            array_push($revewids, $review['review_id']);
        }        

        $entityPkValue = '&'.$entityPkValue.'&';
        
        if(count($revewids) > 0)
        {
            $select = $adapter->select()
            ->from($this->_reviewDetailTable,
                array(
                    'review_count' => new Zend_Db_Expr('COUNT(*)')
                ))
            ->where("INSTR({$this->_reviewDetailTable}.related_product_ids, :pk_value) > 0")
            ->where('review_id NOT IN (?)', $revewids);    
        }else{
            $select = $adapter->select()
            ->from($this->_reviewDetailTable,
                array(
                    'review_count' => new Zend_Db_Expr('COUNT(*)')
                ))
            ->where("INSTR({$this->_reviewDetailTable}.related_product_ids, :pk_value) > 0");
        }
                
        $bind = array(':pk_value' => $entityPkValue);

        $reviews_count = $reviews_count + $adapter->fetchOne($select, $bind);
        return $reviews_count;
    }
    public function getReviews($entityPkValue){
        $entityPkValue = '&'.$entityPkValue.'&';
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_reviewDetailTable, array('review_id'))
            ->where("INSTR({$this->_reviewDetailTable}.related_product_ids, :pk_value) > 0");        
        $bind = array(':pk_value' => $entityPkValue);
        return $adapter->fetchAll($select, $bind);
    }
}