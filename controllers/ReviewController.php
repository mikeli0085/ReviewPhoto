<?php
include("Mage/Adminhtml/controllers/Catalog/Product/ReviewController.php");

class FastDivision_ReviewPhotos_ReviewController extends Mage_Adminhtml_Catalog_Product_ReviewController
{    
    public function editAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Reviews and Ratings'))
             ->_title($this->__('Customer Reviews'));

        $this->_title($this->__('Edit Review'));

        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        if ($reviewId = $this->getRequest()->getParam('id')) {            
            $review = Mage::getModel('review/review')->load($reviewId);
        }
        Mage::register('review_data', $review);
        $this->_addContent($this->getLayout()->createBlock('reviewphotos/manage_review_edit'))
             ->_addLeft($this->getLayout()->createBlock('reviewphotos/manage_review_edit_tabs'));

        $this->renderLayout();
    }
    public function productsAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('review.edit.tab.product')
                ->setReviewProducts($this->getRequest()->getPost('review_related_product_ids', null));
        $this->renderLayout();
    }
    public function productsgridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('review.edit.tab.product')
                ->setReviewProducts($this->getRequest()->getPost('review_related_product_ids', null));
        $this->renderLayout();
    }
    
    public function saveAction()
    {
        if (($data = $this->getRequest()->getPost()) && ($reviewId = $this->getRequest()->getParam('id'))) {            
            $review = Mage::getModel('review/review')->load($reviewId);

            if (! $review->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('The review was removed by another user or does not exist.'));
            } else {
                try {
                    
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

                    if(isset($data['photo']) && !isset($data['photo_url'])) {
                        $data['photo_url'] = $data['photo']['value'];
                    }
                    if(isset($data['photo2']) && !isset($data['photo_url2'])) {
                        $data['photo_url2'] = $data['photo2']['value'];
                    }
                    if(isset($data['photo3']) && !isset($data['photo_url3'])) {
                        $data['photo_url3'] = $data['photo3']['value'];
                    }
                    if(isset($data['photo4']) && !isset($data['photo_url4'])) {
                        $data['photo_url4'] = $data['photo4']['value'];
                    }
                    if(isset($data['photo5']) && !isset($data['photo_url5'])) {
                        $data['photo_url5'] = $data['photo5']['value'];
                    }
                    if(isset($data['photo6']) && !isset($data['photo_url6'])) {
                        $data['photo_url6'] = $data['photo6']['value'];
                    }

                    if(isset($data['photo']['delete']) && $data['photo']['delete'] == 1) {
                        $data['photo_url'] = '';
                    }
                    if(isset($data['photo2']['delete']) && $data['photo2']['delete'] == 1) {
                        $data['photo_url2'] = '';
                    }
                    if(isset($data['photo3']['delete']) && $data['photo3']['delete'] == 1) {
                        $data['photo_url3'] = '';
                    }
                    if(isset($data['photo4']['delete']) && $data['photo4']['delete'] == 1) {
                        $data['photo_url4'] = '';
                    }
                    if(isset($data['photo5']['delete']) && $data['photo5']['delete'] == 1) {
                        $data['photo_url5'] = '';
                    }
                    if(isset($data['photo6']['delete']) && $data['photo6']['delete'] == 1) {
                        $data['photo_url6'] = '';
                    }
                    $data['entity_id'] = $review->getEntityId();
                    if(isset($data['related_product_ids']))
                    {
                        if(substr($data['related_product_ids'], 0, 1) != '&'){
                            $data['related_product_ids'] = '&'.$data['related_product_ids'];
                        }                        
                        if(substr($data['related_product_ids'], strlen($data['related_product_ids']) - 1) != '&'){
                            $data['related_product_ids'] = $data['related_product_ids'].'&';
                        }
                    }                    
                    // Save Review Data
                    $review->addData($data)->save();

                    $arrRatingId = $this->getRequest()->getParam('ratings', array());
                    $votes = Mage::getModel('rating/rating_option_vote')
                        ->getResourceCollection()
                        ->setReviewFilter($reviewId)
                        ->addOptionInfo()
                        ->load()
                        ->addRatingOptions();
                    foreach ($arrRatingId as $ratingId=>$optionId) {
                        if($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            Mage::getModel('rating/rating')
                                ->setVoteId($vote->getId())
                                ->setReviewId($review->getId())
                                ->updateOptionVote($optionId);
                        } else {
                            Mage::getModel('rating/rating')
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->addOptionVote($optionId, $review->getEntityPkValue());
                        }
                    }

                    $review->aggregate();

                    if(isset($data['related_product_ids'])){
                        $this->saveReview($data);
                    }

                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The review has been saved.'));
                } catch (Exception $e){
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }

            return $this->getResponse()->setRedirect($this->getUrl($this->getRequest()->getParam('ret') == 'pending' ? '*/*/pending' : '*/*/'));
        }
        $this->_redirectReferer();
    }
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
    public function saveReview($data)
    {
        $productIds = explode('&',$data['related_product_ids']);
        foreach ($productIds as $productId) {
            if($productId == '') {
                continue;
            }
            if(isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }

            $data['product_id'] = $productId;            
            $review = Mage::getModel('review/review')->setData($data);

            $product = Mage::getModel('catalog/product')
                ->load($productId);

            try {
                $review->setEntityId(1) // product
                    ->setId($this->getRequest()->getParam('id'))
                    ->setEntityPkValue($productId)
                    ->setStoreId($product->getStoreId())
                    ->setStatusId($data['status_id'])
                    ->setCustomerId(null);//null is for administrator only    

                $ratingCollection = Mage::getModel('rating/rating_option_vote')->getResourceCollection()    
                    ->addFieldToFilter('entity_pk_value', $productId);
                
                if(isset($ratingCollection) && count($ratingCollection) > 0) {
                    $bnew = false;
                }else{
                    $bnew = true;
                }                
                
                if($bnew){
                    $arrRatingId = $this->getRequest()->getParam('ratings', array());
                    foreach ($arrRatingId as $ratingId=>$optionId) {
                        Mage::getModel('rating/rating')
                           ->setRatingId($ratingId)
                           ->setReviewId($review->getId())
                           ->addOptionVote($optionId, $productId);
                    }
                }else{
                    $arrRatingId = $this->getRequest()->getParam('ratings', array());
                    $votes = Mage::getModel('rating/rating_option_vote')
                        ->getResourceCollection()
                        ->addOptionInfo()           
                        ->addFieldToFilter('entity_pk_value', $productId)
                        ->addFieldToFilter('review_id', $review->getReviewId())
                        ->load()                   
                        ->addRatingOptions();
                    
                    foreach ($arrRatingId as $ratingId=>$optionId) {
                        if($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            Mage::getModel('rating/rating')
                                ->setVoteId($vote->getId())
                                ->setReviewId($review->getId())
                                ->updateOptionVote($optionId);
                        } else {
                            Mage::getModel('rating/rating')
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->addOptionVote($optionId, $review->getEntityPkValue());
                        }
                    }
                }                

                $review->aggregate();
            } catch (Exception $e){
                die($e->getMessage());
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        return;
    }
    public function postAction()
    {
        $productId = $this->getRequest()->getParam('product_id', false);
        if ($data = $this->getRequest()->getPost()) {
            if(isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }
            
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

            if(isset($data['photo']['delete']) && $data['photo']['delete'] == 1) {
                $data['photo_url'] = '';
            }
            if(isset($data['photo2']['delete']) && $data['photo2']['delete'] == 1) {
                $data['photo_url2'] = '';
            }
            if(isset($data['photo3']['delete']) && $data['photo3']['delete'] == 1) {
                $data['photo_url3'] = '';
            }
            if(isset($data['photo4']['delete']) && $data['photo4']['delete'] == 1) {
                $data['photo_url4'] = '';
            }
            if(isset($data['photo5']['delete']) && $data['photo5']['delete'] == 1) {
                $data['photo_url5'] = '';
            }
            if(isset($data['photo6']['delete']) && $data['photo6']['delete'] == 1) {
                $data['photo_url6'] = '';
            }
            
            $review = Mage::getModel('review/review')->setData($data);

            $product = Mage::getModel('catalog/product')
                ->load($productId);

            try {                
                $review->setEntityId(1) // product
                    ->setEntityPkValue($productId)
                    ->setStoreId($product->getStoreId())
                    ->setStatusId($data['status_id'])
                    ->setCustomerId(null)//null is for administrator only
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $ratingId=>$optionId) {
                    Mage::getModel('rating/rating')
                       ->setRatingId($ratingId)
                       ->setReviewId($review->getId())
                       ->addOptionVote($optionId, $productId);
                }

                $review->aggregate();

                // if(isset($data['related_product_ids'])){
                //     $this->saveReview($data);
                // }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The review has been saved.'));
                if( $this->getRequest()->getParam('ret') == 'pending' ) {
                    $this->getResponse()->setRedirect($this->getUrl('*/*/pending'));
                } else {
                    $this->getResponse()->setRedirect($this->getUrl('*/*/'));
                }

                return;
            } catch (Exception $e){
                die($e->getMessage());
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
        return;
    }
}