<?php
include("Mage/Review/controllers/ProductController.php");

class FastDivision_ReviewPhotos_ProductController extends Mage_Review_ProductController
{
    /**
    * Upload Review Photo
    *
    */
    public function uploadPhoto($photoName)
    {
        try {
            $uploader = new Varien_File_Uploader($photoName);
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));

            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);

            $ext = pathinfo($_FILES[$photoName]['name'], PATHINFO_EXTENSION);
            $newFileName = basename($_FILES[$photoName]['name'], '.' . $ext) . uniqid() . '.' . $ext;
            $path = Mage::getBaseDir('media') . DS . 'reviews';

            $uploader->save($path, $newFileName);           
          } catch(Exception $e) { }
          return $newFileName;
    }
    /**
     * Submit new review action
     *
     */
    public function postAction()
    {
        if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data   = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }

        if (($product = $this->_initProduct()) && !empty($data)) {
            // Review Photo File Upload            
            if(isset($_FILES['photo']['name'])) {                     
                $newFileName = $this->uploadPhoto('photo');
                $data['photo_url'] = $newFileName;         
            }
            if(isset($_FILES['photo2']['name'])) { 
                $newFileName = $this->uploadPhoto('photo2');
                $data['photo_url2'] = $newFileName;                     
            }
            if(isset($_FILES['photo3']['name'])) {                     
                $newFileName = $this->uploadPhoto('photo3');
                $data['photo_url3'] = $newFileName;                     
            }
            if(isset($_FILES['photo4']['name'])) {                     
                $newFileName = $this->uploadPhoto('photo4');
                $data['photo_url4'] = $newFileName;                     
            }
            if(isset($_FILES['photo5']['name'])) {                     
                $newFileName = $this->uploadPhoto('photo5');
                $data['photo_url5'] = $newFileName;                     
            }
            if(isset($_FILES['photo6']['name'])) {                     
                $newFileName = $this->uploadPhoto('photo6');
                $data['photo_url6'] = $newFileName;                     
            }            
            $session    = Mage::getSingleton('core/session');
            /* @var $session Mage_Core_Model_Session */
            $review     = Mage::getModel('review/review')->setData($data);
            /* @var $review Mage_Review_Model_Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->setStores(array(Mage::app()->getStore()->getId()))
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $session->addSuccess($this->__('Your review has been accepted for moderation.'));
                }
                catch (Exception $e) {
                    $session->setFormData($data);
                    $session->addError($this->__('Unable to post the review.'));
                }
            }
            else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                }
                else {
                    $session->addError($this->__('Unable to post the review.'));
                }
            }
        }

        if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
            $this->_redirectUrl($redirectUrl);
            return;
        }
        $this->_redirectReferer();
    }
    public function getReviewsCollection($categoryid, $sortway, $show)
    {
        $_helper       = Mage::helper('catalog/category');
        if($categoryid == 0){ $categoryid = 4; } 

        $productIds = array();
        $category = Mage::getModel('catalog/category')->load($categoryid);
        $products = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addAttributeToFilter('status', 1)
            ->addCategoryFilter($category);
        foreach ($products as $product):
            if (!in_array($product->getId(), $productIds)) {
                array_push($productIds, $product->getId());
            }
        endforeach;
                
        if(strpos($sortway, 'rate') !== false)
        {
            $sortway = str_replace('rate_', '', $sortway);
            if($show == '1'){
                $reviewlist = Mage::getModel('review/review')->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addFieldToFilter('main_table.entity_pk_value', array('in' => $productIds))
                ->addFieldToFilter(array('photo_url', 'photo_url2', 'photo_url3', 'photo_url4', 'photo_url5', 'photo_url6'),
                        array(array('neq' => 'NULL'), array('neq' => 'NULL'), array('neq' => 'NULL'), 
                              array('neq' => 'NULL'), array('neq' => 'NULL'), array('neq' => 'NULL')));
            }else{
                $reviewlist = Mage::getModel('review/review')->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addFieldToFilter('main_table.entity_pk_value', array('in' => $productIds));
            }
            
            $reviewlist->getSelect()->join( array('rating'=>'mgnt_rating_option_vote'), 'main_table.review_id = rating.review_id AND main_table.entity_pk_value = rating.entity_pk_value', array('rating.percent', 'percent'));
            $reviewlist->setOrder('percent',$sortway);
        }else{

            $sortway = str_replace('date_', '', $sortway);        
            if($show == '1'){
                $reviewlist = Mage::getModel('review/review')->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addFieldToFilter('entity_pk_value', array('in' => $productIds))
                ->addFieldToFilter(array('photo_url', 'photo_url2', 'photo_url3', 'photo_url4', 'photo_url5', 'photo_url6'),
                        array(array('neq' => 'NULL'), array('neq' => 'NULL'), array('neq' => 'NULL'), 
                              array('neq' => 'NULL'), array('neq' => 'NULL'), array('neq' => 'NULL')))
                ->setOrder('created_at', $sortway);                
            }else{                
                $reviewlist = Mage::getModel('review/review')->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addFieldToFilter('entity_pk_value', array('in' => $productIds))                
                ->setOrder('created_at', $sortway);            
            }            
        }
         
        return $reviewlist;
    }
    public function ajaxreviewAction()
    {
        $categorid = $this->getRequest()->getParam('category_id');
        $sortway = $this->getRequest()->getParam('sort_method');
        $show = $this->getRequest()->getParam('show_picture');
        $curpage = $this->getRequest()->getParam('current_page');
        $limitcount = $this->getRequest()->getParam('limit_count');
        $this->loadLayout();
        $result = $this->getReviewsCollection($categorid, $sortway, $show);
        if(!is_null($result))
        {
            $result = $result->setPageSize($limitcount)->setCurPage($curpage); 
        }
        // $reviews = $result->getItems();
        // foreach ($reviews as $key => $review) {
        //     var_dump($review->getReviewId());
        // }            
        // exit();
        $response = array();
        $block = $this->getLayout()->getBlock('reviewlist');
        $block->assign('reviewlist', $result);
        $response = $block->toHtml();
        if($toolbar = $this->getLayout()->getBlock('product_review_list.toolbar')){
            $toolbar->setCollection($result);                      
            $response = $toolbar->toHtml().$response.$toolbar->toHtml();                
        }
        //$this->getResponse()->setHeader('Content-Type', 'application/json')->setBody(Mage::helper('core')->jsonEncode($response));
        $this->getResponse()->setHeader('Content-Type', 'text/html')
                ->setBody($response);
    }
    public function ajaxloadmoreAction()
    {
        $limitcount = 10;
        $curpage = $this->getRequest()->getParam('current_page') + 1;
        $productid = $this->getRequest()->getParam('product_id');
        $action_btn = $this->getRequest()->getParam('action_btn');
        $pager = $this->getRequest()->getParam('pager');
        
        $reviewlist = Mage::getModel('review/review')->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->addEntityFilter('product', $productid)
            ->setDateOrder();
        $reviewIds = array();
        foreach ($reviewlist as $key => $review) {  
            array_push($reviewIds, $review->getReviewId());
        }
        
        $_result = Mage::getResourceModel('ReviewPhotos/review')->getReviews($productid);
        foreach ($_result as $key => $_item) {
            array_push($reviewIds, $_item['review_id']);
        }
        
        $result = Mage::getModel('review/review')->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addFieldToFilter('main_table.review_id', array('in' => $reviewIds))
                ->setDateOrder();
        $pages = $result->getSize();
        if(!is_null($result))
        {
            $result = $result->setPageSize($limitcount)->setCurPage($curpage); 
        }
        $response = array();
        $this->loadLayout();
        if($pages > 10){
            $block = $this->getLayout()->getBlock('product_reviews');
			$block = $this->getLayout()->createBlock('core/template')->setTemplate('custom/product_reviews.phtml');
            $block->assign('reviewlist', $result);
            $block->assign('curpage', $curpage);
            $response = $block->toHtml();
            if($pages % 10 > 0){
                $pages = number_format($pages / 10) + 1;
            }else{
                $pages = number_format($pages / 10);
            }
            if($curpage == $pages)
                $response = $response.'<script>jQuery("'.$action_btn.'").css("pointer-events","none");</script>';
            $response = $response.'<script>jQuery("'.$pager.'").val('.$curpage.')</script>';
        }else{
            $response = '<script>jQuery("'.$action_btn.'").css("pointer-events","none");</script>';
        }
        $this->getResponse()->setHeader('Content-Type', 'text/html')
                ->setBody($response);
    }
}
