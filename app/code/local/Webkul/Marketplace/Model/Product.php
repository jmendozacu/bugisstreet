<?php

class Webkul_Marketplace_Model_Product extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('marketplace/product');
    }
	/* save products */
	public function saveSimpleNewProduct($wholedata){
		$cats=array();
		foreach($wholedata['category'] as $keycat){
			array_push($cats,$keycat);
		}
        /*foreach($wholedata['shippingmethodxxx'] as $keyship){
            array_push($shippings,$keyship);
        }*/

		if(isset($wholedata['status']) && isset($wholedata['wstoreids']) ){
			$status=1; 
			$stores=$wholedata['wstoreids'];
		}
		else{		
			$status=Mage::getStoreConfig('marketplace/marketplace_options/product_approval')? 2:1;
			$stores=Mage::app()->getStore()->getStoreId();
		}
		$magentoProductModel = Mage::getModel('catalog/product');
		$magentoProductModel->setData($wholedata);
		$saved=$magentoProductModel->save();
		$magentoProductModel = Mage::getModel('catalog/product')->load($saved->getId());
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
		$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
		if(!$rates[$currentCurrencyCode]){
			$rates[$currentCurrencyCode] = 1;
		}
		if($wholedata['special_price']){
			$special_price = $wholedata['special_price']/$rates[$currentCurrencyCode];
			$magentoProductModel->setSpecialPrice($special_price);
		}
		$price = $wholedata['price']/$rates[$currentCurrencyCode];
		$magentoProductModel->setPrice($price);
		$magentoProductModel->setWeight($wholedata['weight']);
		$magentoProductModel->setStoresIds(array($stores));
		$storeId = Mage::app()->getStore()->getId();
		$magentoProductModel->setWebsiteIds(array(Mage::getModel('core/store')->load( $storeId )->getWebsiteId()));
		$magentoProductModel->setCategoryIds($cats);
		$magentoProductModel->setStatus($status);
		$saved=$magentoProductModel->save();
		$lastId = $saved->getId();
		$this->_saveStock($lastId,$wholedata['stock'],$wholedata['is_in_stock']); 
		$wholedata['id'] = $lastId;
		/*Mage::dispatchEvent('mp_customoption_setdata', $wholedata);*/
		$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		$collection1=Mage::getModel('marketplace/product');
		$collection1->setmageproductid($lastId);
		$collection1->setuserid($vendorId);
		$collection1->setstatus($status);
        $collection1->setAllowedShipping(implode(',', $wholedata['shippingmethod']));
		$collection1->save();
		if(!is_dir(Mage::getBaseDir().'/media/marketplace/')){
			mkdir(Mage::getBaseDir().'/media/marketplace/', 0755);
		}
		if(!is_dir(Mage::getBaseDir().'/media/marketplace/'.$lastId.'/')){
			mkdir(Mage::getBaseDir().'/media/marketplace/'.$lastId.'/', 0755);
		}
		$target =Mage::getBaseDir().'/media/marketplace/'.$lastId.'/';

		$arr_images_res = array();
		$arr_images = $_FILES['images'];
		for($i=0;$i<count($arr_images['name']);$i++){
			$arr_images_res['images'.$i]['name'] = $arr_images['name'][$i];
			$arr_images_res['images'.$i]['type'] = $arr_images['type'][$i];
			$arr_images_res['images'.$i]['tmp_name'] = $arr_images['tmp_name'][$i];
			$arr_images_res['images'.$i]['error'] = $arr_images['error'][$i];
			$arr_images_res['images'.$i]['size'] = $arr_images['size'][$i];
		}
		if(isset($_FILES) && count($_FILES) > 0){
			if($wholedata['type_id']=='simple' || $wholedata['type_id']=='bundle'){
				foreach($arr_images_res as $image ){
					if($image['tmp_name'] != ''){
						$splitname = explode('.', $image['name']);
						$splitname[0] = str_replace('-', '', $splitname[0]);
						$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
						$target1 = $target.$image_name.".".$splitname[1];
						move_uploaded_file($image['tmp_name'],$target1);
					}
				}
			}
		}
//		if($wholedata['defaultimage']){
//			$splitname = explode('.', $wholedata['defaultimage']);
//			if($splitname[1]){
//				$splitname[0] = str_replace('-', '', $splitname[0]);
//				$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
//				$wholedata['defaultimage'] = $image_name.".".$splitname[1];
//			}
//		}

	//	$this->_addImages($lastId,$wholedata['defaultimage']);
        ///

        if($wholedata['defaultimage']){

//			$splitname = explode('.', $wholedata['defaultimage']);
//			if($splitname[1]){
//				$splitname[0] = str_replace('-', '', $splitname[0]);
//				$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
//				$wholedata['defaultimage'] = $image_name.".".$splitname[1];
//			}
            $wholedata['defaultimage'] = basename($wholedata['defaultimage']);
        }
        //$this->_addImages($lastId,$wholedata['defaultimage']);
        $this->_addImagesMulti($lastId,$wholedata['defaultimage'],$wholedata['upload_images']);
        //now set the position

        $_product_images = Mage::getModel('catalog/product')->load($lastId)->getMediaGalleryImages()->getItems();
        //var_dump($_product_images);
        if(isset($wholedata['position'])) {
            $arrTmpPos = array();
            foreach($wholedata['position'] as $pos=>$valpos){
                $filename = explode('.',basename($pos));
                $filename = $filename[0];
                foreach($_product_images as $pimg)
                {
                    if (strpos($pimg->getFile(), $filename) !== FALSE){
                        $arrTmpPos[$pimg->getData('value_id')] = $valpos;
                    }
                }
            }
            //var_dump($arrTmpPos);die;
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $table = 'catalog_product_entity_media_gallery_value';
            //$_position = $wholedata['position'];
            foreach ($arrTmpPos as $key => $val) {
                $query = 'UPDATE ' . $table . ' SET position = ' . $val . ' WHERE value_id = ' . $key;
                $writeConnection->query($query);
            }
        }

        ///
		Mage::dispatchEvent('mp_customattribute_settierpricedata', $wholedata);

		return $lastId;		
	}
	public function saveDownloadableNewProduct($wholedata){
		$cats=array();
		foreach($wholedata['category'] as $keycat){
			array_push($cats,$keycat);
		}
		if(isset($wholedata['status']) && isset($wholedata['wstoreids']) ){
			$status=1; 
			$stores=$wholedata['wstoreids'];
		}
		else{		
			$status=Mage::getStoreConfig('marketplace/marketplace_options/product_approval')? 2:1;
			$stores=Mage::app()->getStore()->getStoreId();
		}				
		$magentoProductModel = Mage::getModel('catalog/product');
		$magentoProductModel->setData($wholedata);
		$saved=$magentoProductModel->save();
		$magentoProductModel = Mage::getModel('catalog/product')->load($saved->getId());
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
		$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
		if(!$rates[$currentCurrencyCode]){
			$rates[$currentCurrencyCode] = 1;
		}
		if($wholedata['special_price']){
			$special_price = $wholedata['special_price']/$rates[$currentCurrencyCode];
			$magentoProductModel->setSpecialPrice($special_price);
		}
		$price = $wholedata['price']/$rates[$currentCurrencyCode];
		$magentoProductModel->setPrice($price);
		$magentoProductModel->setStoresIds(array($stores));
		$storeId = Mage::app()->getStore()->getId();
		$magentoProductModel->setWebsiteIds(array(Mage::getModel('core/store')->load( $storeId )->getWebsiteId()));
		$magentoProductModel->setCategoryIds($cats);
		$magentoProductModel->setStatus($status);
		$saved=$magentoProductModel->save();
		$lastId = $saved->getId();
		$this->_saveStock($lastId,10000,$wholedata['is_in_stock']); 
		
		$wholedata['id'] = $lastId;
		Mage::dispatchEvent('mp_customoption_setdata', $wholedata);
		$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		$collection1=Mage::getModel('marketplace/product');
		$collection1->setmageproductid($lastId);		
		$collection1->setuserid($vendorId);
		$collection1->setstatus($status);
		$collection1->save();
		if(isset($_FILES) && count($_FILES) > 0)	{
			$cp_image_dir = Mage::getBaseDir()."/media/marketplace/";
			$this_sellerid_path = Mage::getBaseDir()."/media/marketplace/".$lastId."/";
			$this_image_path = Mage::getBaseDir()."/media/marketplace/".$lastId."/images/";
			$this_sample_path = Mage::getBaseDir()."/media/marketplace/".$lastId."/sample/";
			$this_download_path = Mage::getBaseDir()."/media/marketplace/".$lastId."/download/";	
			$this_mainsample_path = Mage::getBaseDir()."/media/marketplace/".$lastId."/mainsample/";				
			if (!is_dir($cp_image_dir))
				mkdir($cp_image_dir, 0755);

			if (!is_dir($this_sellerid_path))
				mkdir($this_sellerid_path, 0755);

			if (!is_dir($this_image_path))
				mkdir($this_image_path, 0755);

			if (!is_dir($this_sample_path))
				mkdir($this_sample_path, 0755);

			if (!is_dir($this_download_path))
				mkdir($this_download_path, 0755);
				
			if (!is_dir($this_mainsample_path))
				mkdir($this_mainsample_path, 0755);	

			foreach($_FILES["images"]["tmp_name"] as $key => $value){
				$splitname = explode('.', $_FILES["images"]['name'][$key]);
				$splitname[0] = str_replace('-', '', $splitname[0]);
				$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
				$target1 = $this_image_path.$image_name.".".$splitname[1];
				move_uploaded_file($value,$target1);
			}
			foreach($_FILES["sample"]["tmp_name"]  as $key => $value){
				if($_FILES['sample']['tmp_name'] != '' )
					move_uploaded_file($value,$this_sample_path.$_FILES['sample']['name'][$key]);
			}
			foreach($_FILES["wk_link"]["tmp_name"]  as $key => $value){
				if($_FILES['wk_link']['tmp_name'] != '' )
					move_uploaded_file($value,$this_download_path.$_FILES['wk_link']['name'][$key]);
			}
			foreach($_FILES["wk_samples"]["tmp_name"]  as $key => $value){
				if($_FILES['wk_samples']['tmp_name'] != '' )
					move_uploaded_file($value,$this_mainsample_path.$_FILES['wk_samples']['name'][$key]);
			}
		}

		if($wholedata['defaultimage']){
			$splitname = explode('.', $wholedata['defaultimage']);
			if($splitname[1]){
				$splitname[0] = str_replace('-', '', $splitname[0]);
				$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
				$wholedata['defaultimage'] = $image_name.".".$splitname[1]; 
			}
		}
		
		$this->AddImages($lastId,$wholedata['link'],$wholedata['defaultimage'],$rates,$currentCurrencyCode,$wholedata['samples']);

		Mage::dispatchEvent('mp_customattribute_settierpricedata', $wholedata);

		return $lastId;		
	}
	public function saveVirtualNewProduct($wholedata){
		$cats=array();
		foreach($wholedata['category'] as $keycat){
			array_push($cats,$keycat);
		}
		if(isset($wholedata['status']) && isset($wholedata['wstoreids']) ){
			$status=1; 
			$stores=$wholedata['wstoreids'];
		}
		else{
			$status=Mage::getStoreConfig('marketplace/marketplace_options/product_approval')? 2:1;
			$stores=Mage::app()->getStore()->getStoreId();
		}				
		$magentoProductModel = Mage::getModel('catalog/product');
		$magentoProductModel->setData($wholedata);
		$saved=$magentoProductModel->save();
		$magentoProductModel = Mage::getModel('catalog/product')->load($saved->getId());
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
		$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
		if(!$rates[$currentCurrencyCode]){
			$rates[$currentCurrencyCode] = 1;
		}
		if($wholedata['special_price']){
			$special_price = $wholedata['special_price']/$rates[$currentCurrencyCode];
			$magentoProductModel->setSpecialPrice($special_price);
		}
		$price = $wholedata['price']/$rates[$currentCurrencyCode];
		$magentoProductModel->setPrice($price);
		$magentoProductModel->setStoresIds(array($stores));
		$storeId = Mage::app()->getStore()->getId();
		$magentoProductModel->setWebsiteIds(array(Mage::getModel('core/store')->load( $storeId )->getWebsiteId()));
		$magentoProductModel->setCategoryIds($cats);
		$magentoProductModel->setStatus($status);
		$saved=$magentoProductModel->save();
		$lastId = $saved->getId();
		$this->_saveStock($lastId,$wholedata['stock'],$wholedata['is_in_stock']);
		
		$wholedata['id'] = $lastId;
		Mage::dispatchEvent('mp_customoption_setdata', $wholedata);
		$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		$collection1=Mage::getModel('marketplace/product');
		$collection1->setmageproductid($lastId);
		$collection1->setuserid($vendorId);
		$collection1->setstatus($status);
		$collection1->save();
			
		if($wholedata['type_id']=='virtual'){
		if((isset($_FILES) && count($_FILES) > 0) && !isset($wholedata['csv'])){
				if (!is_dir(Mage::getBaseDir().'/media/marketplace/')){
					mkdir(Mage::getBaseDir().'/media/marketplace/', 0755);
				}
				if (!is_dir(Mage::getBaseDir().'/media/marketplace/'.$lastId)){
					mkdir(Mage::getBaseDir().'/media/marketplace/'.$lastId, 0755);
				}
				foreach($_FILES as $image){
					$imagesdir = Mage::getBaseDir().'/media/marketplace/'.$lastId.'/';
					$splitname = explode('.', $image['name']);
					$splitname[0] = str_replace('-', '', $splitname[0]);
					$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
					$target1 = $imagesdir.$image_name.".".$splitname[1];			
					move_uploaded_file($image['tmp_name'],$target1);	
				}
				if($wholedata['defaultimage']){
					$splitname = explode('.', $wholedata['defaultimage']);
					if($splitname[1]){
						$splitname[0] = str_replace('-', '', $splitname[0]);
						$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
						$wholedata['defaultimage'] = $image_name.".".$splitname[1]; 
					}
				}
				if($wholedata['defaultimage']){
				 $this->_addImages($lastId,$wholedata['defaultimage']);
			    }
	        }
		}
		Mage::dispatchEvent('mp_customattribute_settierpricedata', $wholedata);
		return $lastId;		
	}

	public function saveConfigNewProduct($wholedata){
		$attr=explode(',',$wholedata['supperattr']);
		unset($attr[count($attr)-1]);
		//var_dump($wholedata);die;
		if(isset($wholedata['status']) && isset($wholedata['wstoreids']) ){
			$status=1; 
			$stores=$wholedata['wstoreids'];
		}
		else{		
			$status=Mage::getStoreConfig('marketplace/marketplace_options/product_approval')? 2:1;
			$stores=Mage::app()->getStore()->getStoreId();
		}
//		foreach($wholedata['position'] as $pos=>$valpos) {
//			$filename = explode('.',basename($pos));
//			$filename = $filename[0];
//			var_dump($filename);
//		}die;
		$magentoProductModel = Mage::getModel('catalog/product');
		$magentoProductModel->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
		$magentoProductModel->setData($wholedata);
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
		$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
		if(!$rates[$currentCurrencyCode]){
			$rates[$currentCurrencyCode] = 1;
		}
		if($wholedata['special_price']){
			$special_price = $wholedata['special_price']/$rates[$currentCurrencyCode];
			$magentoProductModel->setSpecialPrice($special_price);
		}
		$price = $wholedata['price']/$rates[$currentCurrencyCode];
		$magentoProductModel->setPrice($price);
		$magentoProductModel->setStoresIds(array($stores));
		$storeId = Mage::app()->getStore()->getId();
		$magentoProductModel->setWebsiteIds(array(Mage::getModel('core/store')->load( $storeId )->getWebsiteId()));
		
		$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product',$attr[0]); 
		$magentoProductModel->getTypeInstance()->setUsedProductAttributeIds(array($attributeId));
		if (array_key_exists('asso_pro', $wholedata)) {
		$asspro = $wholedata['asso_pro'];
				 $data[$asspro] = array();
		}		 
		foreach($attr as $attrCode){
	        $super_attribute= Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product',$attrCode);
	        $configurableAtt = Mage::getModel('catalog/product_type_configurable_attribute')->setProductAttribute($super_attribute);
	 		 $newAttributes[] = array(
	           'id'             => $configurableAtt->getId(),
	           'label'          => $configurableAtt->getLabel(),
	           'position'       => $super_attribute->getPosition(),
	           'values'         => $configurableAtt->getPrices() ? $configProduct->getPrices() : array(),
	           'attribute_id'   => $super_attribute->getId(),
	           'attribute_code' => $super_attribute->getAttributeCode(),
	           'frontend_label' => $super_attribute->getFrontend()->getLabel(),
	        );
	    }
		$magentoProductModel->setConfigurableAttributesData($newAttributes);
		$saved=$magentoProductModel->save();
		$magentoProductModel = Mage::getModel('catalog/product')->load($saved->getId());
		$magentoProductModel->setCategoryIds(Array($wholedata['category']));
		$magentoProductModel->setStatus($status);
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
		$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
		if(!$rates[$currentCurrencyCode]){
			$rates[$currentCurrencyCode] = 1;
		}
		if($wholedata['special_price']){
			$special_price = $wholedata['special_price']/$rates[$currentCurrencyCode];
			$magentoProductModel->setSpecialPrice($special_price);
		}
		$price = $wholedata['price']/$rates[$currentCurrencyCode];
		$magentoProductModel->setPrice($price);
		// $magentoProductModel->setTaxClassId(0);
		$magentoProductModel->setStockData(array(
	        'use_config_manage_stock' => 1,
	        'is_in_stock' => $wholedata['is_in_stock'],
	        'is_salable' => 1,
	    ));
		$saved=$magentoProductModel->save();
		$lastId = $saved->getId();
		$stockStatus = Mage::getModel('cataloginventory/stock_status');
		$stockStatus->assignProduct($magentoProductModel);
		$stockStatus->saveProductStatus($magentoProductModel->getId(), 1);
		$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		$collection1=Mage::getModel('marketplace/product');
		$collection1->setmageproductid($lastId);
		$collection1->setuserid($vendorId);
		$collection1->setstatus($status);
        $collection1->setAllowedShipping(implode(',', $wholedata['shippingmethod']));
		$collection1->save();
		
		$wholedata['id'] = $lastId;
		/*Mage::dispatchEvent('mp_customoption_setdata', $wholedata);*/

//		if(!is_dir(Mage::getBaseDir().'/media/marketplace/')){
//			mkdir(Mage::getBaseDir().'/media/marketplace/', 0755);
//		}
//		if(!is_dir(Mage::getBaseDir().'/media/marketplace/'.$lastId.'/')){
//			mkdir(Mage::getBaseDir().'/media/marketplace/'.$lastId.'/', 0755);
//		}
//		$target =Mage::getBaseDir().'/media/marketplace/'.$lastId.'/';

//		$arr_images_res = array();
//		$arr_images = $_FILES['images'];
//		for($i=0;$i<count($arr_images['name']);$i++){
//			$arr_images_res['images'.$i]['name'] = $arr_images['name'][$i];
//			$arr_images_res['images'.$i]['type'] = $arr_images['type'][$i];
//			$arr_images_res['images'.$i]['tmp_name'] = $arr_images['tmp_name'][$i];
//			$arr_images_res['images'.$i]['error'] = $arr_images['error'][$i];
//			$arr_images_res['images'.$i]['size'] = $arr_images['size'][$i];
//		}
//
//		if(isset($_FILES) && count($_FILES) > 0){
//			foreach($arr_images_res as $image ){
//				if($image['tmp_name'] != ''){
//					$splitname = explode('.', $image['name']);
//					$splitname[0] = str_replace('-', '', $splitname[0]);
//					$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
//					$target1 = $target.$image_name.".".$splitname[1];
//					move_uploaded_file($image['tmp_name'],$target1);
//				}
//			}
//		}
		if($wholedata['defaultimage']){

//			$splitname = explode('.', $wholedata['defaultimage']);
//			if($splitname[1]){
//				$splitname[0] = str_replace('-', '', $splitname[0]);
//				$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
//				$wholedata['defaultimage'] = $image_name.".".$splitname[1];
//			}
			$wholedata['defaultimage'] = basename($wholedata['defaultimage']);
		}
		//$this->_addImages($lastId,$wholedata['defaultimage']);
		$this->_addImagesMulti($lastId,$wholedata['defaultimage'],$wholedata['upload_images']);
		//now set the position

		$_product_images = Mage::getModel('catalog/product')->load($lastId)->getMediaGalleryImages()->getItems();
		//var_dump($_product_images);
		if(isset($wholedata['position'])) {
			$arrTmpPos = array();
			foreach($wholedata['position'] as $pos=>$valpos){
				$filename = explode('.',basename($pos));
				$filename = $filename[0];
				foreach($_product_images as $pimg)
				{
					if (strpos($pimg->getFile(), $filename) !== FALSE){
						$arrTmpPos[$pimg->getData('value_id')] = $valpos;
					}
				}
			}
			//var_dump($arrTmpPos);die;
			$resource = Mage::getSingleton('core/resource');
			$writeConnection = $resource->getConnection('core_write');
			$table = 'catalog_product_entity_media_gallery_value';
			//$_position = $wholedata['position'];
			foreach ($arrTmpPos as $key => $val) {
				$query = 'UPDATE ' . $table . ' SET position = ' . $val . ' WHERE value_id = ' . $key;
				$writeConnection->query($query);
			}
		}
		//var_dump($_product_images->getItems());die;

		//$app = Mage::app('admin');
		umask(0);

		Mage::dispatchEvent('mp_customattribute_settierpricedata', $wholedata);

		/*$indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection();
		foreach ($indexingProcesses as $process) {
		      $process->reindexEverything();
		}*/
		return $lastId;
		
	}
	public function quickcreate($wholedata){
		$price = 0;
		$product = Mage::getModel('catalog/product')->load($wholedata['mainid']);
		$childIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($wholedata['mainid']);
		foreach($childIds[0] as $val)
		{
		 $data[$val] = array();
		} 
		$configproducts = Mage::getModel('catalog/product') ->load($wholedata['mainid']);
		$magentoProductModel = Mage::getModel('catalog/product');
		$magentoProductModel->setData($wholedata);
		$magentoProductModel->setName($wholedata['Name']);
		$magentoProductModel->setDescription($product->getDescription());
		$magentoProductModel->setShortDescription($product->getShortDescription());
		$magentoProductModel->setsku($wholedata['Sku']);
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
		$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
		if(!$rates[$currentCurrencyCode]){
			$rates[$currentCurrencyCode] = 1;
		}
		$price = $wholedata['price']/$rates[$currentCurrencyCode];
		$magentoProductModel->setPrice($price);
		$magentoProductModel->setCategoryIds($product->getCategoryIds());
		$magentoProductModel->setStoresIds(array($stores));
		$storeId = Mage::app()->getStore()->getId();
		$magentoProductModel->setWebsiteIds(array(Mage::getModel('core/store')->load( $storeId )->getWebsiteId()));
		$magentoProductModel->setTypeId('simple');
		$magentoProductModel->setAttributeSetId($configproducts->getAttributeSetId());
		$magentoProductModel->setTaxClassId("None");
		$magentoProductModel->setStockData(array(
                'is_in_stock' => 1,
                'qty' => $wholedata['Qty']
                )
            );
		$saved=$magentoProductModel->save();
		$lastid = $saved->getId();
		$data[$lastid] =  array();
		$product->setConfigurableProductsData($data);
		$product->setCanSaveConfigurableAttributes(true);
		$product->save();
		$configattr = Mage::getModel('catalog/product_type_configurable')->getConfigurableAttributesAsArray($configproducts);
		foreach ($wholedata as $key => $value) {
			if(strpos($key,'|') !== false){
				$supattr = explode('|', $key);
				for($i=0;$i<count($configattr);$i++) {

					if($supattr[2]==$configattr[$i]['id']){
						for ($j=0;$j<count($configattr[$i]['values']);$j++) {
							if($configattr[$i]['values'][$j]['value_index']==$supattr[3]){
								$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
								$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
								$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
								$price = $value/$rates[$currentCurrencyCode];
								$configattr[$i]['values'][$j]['pricing_value'] =  $price;
								$configattr[$i]['values'][$j]['can_edit_price'] = 1;
                            	$configattr[$i]['values'][$j]['can_read_price'] = 1;
							}
						}
					}
				}
			}
		}
		$configproducts->setConfigurableAttributesData($configattr);
		$configproducts->save();
		$app = Mage::app('admin');
		umask(0);

		/*$indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection();
		foreach ($indexingProcesses as $process) {
		      $process->reindexEverything();
		}*/
		
		return 0;
	}
	public function editassociate($wholedata){
		$price = 0;
		$product = Mage::getModel('catalog/product')->load($wholedata['mainid']);
		$configproducts = Mage::getModel('catalog/product') ->load($wholedata['mainid']);
		$configattr = Mage::getModel('catalog/product_type_configurable')->getConfigurableAttributesAsArray($configproducts);
		
		for($i=0;$i<count($configattr);$i++) {			
			for ($j=0;$j<count($configattr[$i]['values']);$j++) {
				$updated_price_value = $wholedata['assopro'][$configattr[$i]['attribute_code']][$configattr[$i]['values'][$j]['value_index']]['price'];

				//if($updated_price_value){
					$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
					$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
					$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
					if(!$rates[$currentCurrencyCode]){
						$rates[$currentCurrencyCode] = 1;
					}
					if($rates[$currentCurrencyCode])
						$updated_price_value = $updated_price_value/$rates[$currentCurrencyCode];
					$configattr[$i]['values'][$j]['pricing_value'] =  $updated_price_value;
					$configattr[$i]['values'][$j]['can_edit_price'] = 1;
                	$configattr[$i]['values'][$j]['can_read_price'] = 1;
				//}
			}
		}
		$configproducts->setConfigurableAttributesData($configattr);
		$configproducts->save();
		foreach ($wholedata['prod'] as $key => $value) {
			$qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($key);
			if($value){
				$is_in_stock = 1;
			}else{
				$is_in_stock = 0;
			}
			$qtyStock->setProductId($key)->setStockId(1);
			$qtyStock->setData('is_in_stock', $is_in_stock); 
			$savedStock = $qtyStock->save();
			$qtyStock->load($savedStock->getId())->setQty($value)->save();
			$qtyStock->setProductId($key)->setStockId(1);
			$qtyStock->setData('is_in_stock', $is_in_stock); 
			$savedStock = $qtyStock->save();
		}
		$app = Mage::app('admin');
		umask(0);		
		return 0;
	}

	public function saveassociate($wholedata){
		$product = Mage::getModel('catalog/product')->load($wholedata['mainid']);
		$data = array();
	    
	    foreach ($wholedata['asso_pro'] as $key => $value) {
			//print_r($key);
			//print_r($value);
			//Mage::log($key, null, 'key.log', true);
			//Mage::log($value, null, 'value.log', true);
			//if($value=="on"){
	    		$data[$key] = array();

	    	//}

	    }
		//Mage::log($data, null, 'data.log', true);
	    $product->setConfigurableProductsData($data);
		$product->save();
		$app = Mage::app('admin');
		umask(0);

		/*$indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection();
		foreach ($indexingProcesses as $process) {
		      $process->reindexEverything();
		}*/
	}
	/* end save*/
	public function saveBecomePartnerStatus($wholedata){
		$partnerId=Mage::getSingleton('customer/session')->getCustomerId(); 
		$customer=Mage::getModel('customer/customer')->load($partnerId);
		$status=Mage::getStoreConfig('marketplace/marketplace_options/partner_approval')? 0:1;
		$assinstatus=Mage::getStoreConfig('marketplace/marketplace_options/partner_approval')? "Pending":"Seller";	
		$collection=Mage::getModel('marketplace/userprofile');
		$collection->setMageuserid($partnerId);
		$collection->setPartnerstatus($assinstatus);
		$collection->setWantpartner($status);
		$collection->setProfileurl($wholedata['profileurl']);
		$saved=$collection->save();
		$lastId=$saved->getAutoid();
		if($lastId){
			$email = Mage::getModel('admin/user')->load(1)->getEmail();
			$admin = Mage::getSingleton('admin/session');
			$headers = 'From:Administrator' . "\r\n" .
			'Reply-To: ' .$customer->getemail(). "\r\n" .
			'X-Mailer: PHP/' . phpversion();
			$content = 'A New User '.$customer->getemail().' request to become a partner in your Store';
			mail($email,'User Request For Seller',$content,$headers);
		}
	}
	
	/*edit products*/
	public function editProduct($id,$wholedata){
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

		$wholedata['id'] = $id;
		Mage::dispatchEvent('mp_customattribute_deletetierpricedata', $wholedata);
		$cats=array();
		foreach($wholedata['category'] as $keycat){
			array_push($cats,$keycat);
		}
		//$id= Mage::getSingleton('core/session')->getEditProductId();
		$productcity = Mage::getModel('catalog/product');
		$loadpro = $productcity->load($id);

		foreach($wholedata as $key=>$val)
		{
			$loadpro->setData($key,$val);
		}
		//Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		$saved=$loadpro->save();
		$loadpro = Mage::getModel('catalog/product')->load($saved->getId());
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		
		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
		$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
		if(!$rates[$currentCurrencyCode]){
			$rates[$currentCurrencyCode] = 1;
		}
		if($wholedata['special_price']){
			$special_price = $wholedata['special_price']/$rates[$currentCurrencyCode];
			$loadpro->setSpecialPrice($special_price);
		}
		$price = $wholedata['price']/$rates[$currentCurrencyCode];
		$loadpro->setPrice($price);
		$loadpro->setWeight($wholedata['weight']);
		if($wholedata['category_change']==1)
			$loadpro->setCategoryIds($cats);
		$loadpro->save();
		$qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($id);
		$tierPrices=array();
		$saved=$loadpro->save();
        $lastId = $saved->getId();
		if(Mage::getStoreConfig('marketplace/marketplace_options/product_edit_approval')){
			$sellerid = Mage::getSingleton('customer/session')->getCustomer()->getId();
			$collection = Mage::getModel('marketplace/product')->getCollection()
								->addFieldToFilter('mageproductid',array('eq'=>$id))
								->addFieldToFilter('userid',array('eq'=>$sellerid));
			foreach ($collection as $row) {
				$catarray=$loadpro->getCategoryIds();
				$categoryname='';
				$catagory_model = Mage::getModel('catalog/category');
				foreach($catarray as $keycat){
				$categoriesy = $catagory_model->load($keycat);
					if($categoryname ==''){
						$categoryname=$categoriesy->getName();
					}else{
						$categoryname=$categoryname.",".$categoriesy->getName();
					}
				}
				$allStores = Mage::app()->getStores();
				foreach ($allStores as $_eachStoreId => $val)
				{
					Mage::getModel('catalog/product_status')->updateProductStatus($id,Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
				}
				$loadpro->setStatus(2)->save();
				$row->setStatus(2);
                /*$row->setAllowedShipping(implode(',', $wholedata['shippingmethod']));*/
				$row->save();
			}
			$customer = Mage::getModel('customer/customer')->load($sellerid);
			$emailTemp = Mage::getModel('core/email_template')->loadDefault('approveproduct');
			$emailTempVariables = array();
			$adminname = 'Administrators';
			$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
			$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
			$emailTempVariables['myvar1'] = $loadpro['name'];
			$emailTempVariables['myvar2'] = $categoryname;
			$emailTempVariables['myvar3'] = $adminname;
			$emailTempVariables['myvar4'] = Mage::helper('marketplace')->__('I would like to inform you that recently i have updated a product Please approve it soon.');
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			$emailTemp->setSenderName($customer->getName());
			$emailTemp->setSenderEmail($customer->getEmail());
			$emailTemp->send($adminEmail,$adminname,$emailTempVariables);
		}
		if(!is_dir(Mage::getBaseDir().'/media/marketplace/')){
			mkdir(Mage::getBaseDir().'/media/marketplace/', 0755);
		}
		if(!is_dir(Mage::getBaseDir().'/media/marketplace/'.$id.'/')){
			mkdir(Mage::getBaseDir().'/media/marketplace/'.$id.'/', 0755);
		}
		$target =Mage::getBaseDir().'/media/marketplace/'.$id.'/';
		$arr_images_res = array();
		$arr_images = $_FILES['images'];
		for($i=0;$i<count($arr_images['name']);$i++){
			$arr_images_res['images'.$i]['name'] = $arr_images['name'][$i];
			$arr_images_res['images'.$i]['type'] = $arr_images['type'][$i];
			$arr_images_res['images'.$i]['tmp_name'] = $arr_images['tmp_name'][$i];
			$arr_images_res['images'.$i]['error'] = $arr_images['error'][$i];
			$arr_images_res['images'.$i]['size'] = $arr_images['size'][$i];
		}
		//var_dump($arr_images_res);die;
		if(isset($_FILES) && count($_FILES) > 0){
			foreach($arr_images_res as $image ){
				if($image['tmp_name'] != ''){
					$splitname = explode('.', $image['name']);
					$splitname[0] = str_replace('-', '', $splitname[0]);
					$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
					$target1 = $target.$image_name.".".$splitname[1];
					move_uploaded_file($image['tmp_name'],$target1);
				}
			}				
		}
		if($wholedata['defaultimage']){
			$splitname = explode('.', $wholedata['defaultimage']);
			if($splitname[1]){
				$splitname[0] = str_replace('-', '', $splitname[0]);
				$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
				$wholedata['defaultimage'] = $image_name.".".$splitname[1]; 
			}
		}

		if($wholedata['status']==2){
			$collection1=Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter("mageproductid",$saved->getId());
			foreach ($collection1 as $coll) {
				$coll->setStatus(2);
                /*$coll->setAllowedShipping(implode(',', $wholedata['shippingmethod']));*/
				$coll->save();
			}
			$allStores = Mage::app()->getStores();
			foreach ($allStores as $_eachStoreId => $val)
			{
				Mage::getModel('catalog/product_status')->updateProductStatus($saved->getId(),Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
			}
		}

        // edit shipping method
        $collection2=Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter("mageproductid",$saved->getId());
        foreach ($collection2 as $col2) {
            $col2->setAllowedShipping(implode(',', $wholedata['shippingmethod']));
            $col2->save();
        }

//		$this->_addImages($id,$wholedata['defaultimage']);
        $this->_addImagesMulti($lastId,$wholedata['defaultimage'],$wholedata['upload_images']);
		$qtyStock->setProductId($id)->setStockId(1);
		$qtyStock->setData('is_in_stock', $wholedata['is_in_stock']); 
		$savedStock = $qtyStock->save();
		$qtyStock->load($savedStock->getId())->setQty($wholedata['stock'])->save();
		$qtyStock->setProductId($id)->setStockId(1);
		$qtyStock->setData('is_in_stock', $wholedata['is_in_stock']); 
		$savedStock = $qtyStock->save();
        $_product_images = Mage::getModel('catalog/product')->load($lastId)->getMediaGalleryImages()->getItems();
		//now set the position
        if(isset($wholedata['position'])) {
            $arrTmpPos = array();
            $_position = $wholedata['position'];

            foreach($wholedata['position'] as $pos=>$valpos){
                $filename = explode('.',basename($pos));
                $filename = $filename[0];
                foreach($_product_images as $pimg)
                {
                    if (strpos($pimg->getFile(), $filename) !== FALSE){
                        $arrTmpPos[$pimg->getData('value_id')] = $valpos;
                    }
                }
            }

            $count_new = count($arrTmpPos);
            $count_old = count($_position);
            $count = $count_old - $count_new;
            //var_dump($count); die();
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $table = 'catalog_product_entity_media_gallery_value';
            foreach ($arrTmpPos as $key => $val) {
                $query = 'UPDATE ' . $table . ' SET position = ' . $val . ' WHERE value_id = ' . $key;
                $writeConnection->query($query);
            }
            $i=0;
           foreach ($_position as $key => $val) {
               if($i<$count) {
                   $query = 'UPDATE ' . $table . ' SET position = ' . $val . ' WHERE value_id = ' . $key;
                   $writeConnection->query($query);
               }
               $i++;
            }
        }
		/*Mage::dispatchEvent('mp_customoption_setdata', $wholedata);*/
		Mage::dispatchEvent('mp_customattribute_settierpricedata', $wholedata);

		return 0;
	}
	public function editDownloadableProduct($id,$wholedata){
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		
		
		$wholedata['samples'] = array_values($wholedata['samples']);
		$wholedata['link'] = array_values($wholedata['link']);
		
		$wholedata['id'] = $id;
		Mage::dispatchEvent('mp_customattribute_deletetierpricedata', $wholedata);

		$cats=array();
		foreach($wholedata['category'] as $keycat){
			array_push($cats,$keycat);
		}
	    $productcity = Mage::getModel('catalog/product');
		$loadpro = $productcity->load($id);
		foreach($wholedata as $key=>$val)
		{
			$loadpro->setData($key,$val);
		}
		//Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		$saved=$loadpro->save();
		$loadpro = Mage::getModel('catalog/product')->load($saved->getId());
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		
		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
		$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
		if(!$rates[$currentCurrencyCode]){
			$rates[$currentCurrencyCode] = 1;
		}
		if($wholedata['special_price']){
			$special_price = $wholedata['special_price']/$rates[$currentCurrencyCode];
			$loadpro->setSpecialPrice($special_price);
		}
		$price = $wholedata['price']/$rates[$currentCurrencyCode];
		$loadpro->setPrice($price);
		$loadpro->setWeight($wholedata['weight']);
		$loadpro->setCategoryIds($cats);
		$loadpro->save();
		if(Mage::getStoreConfig('marketplace/marketplace_options/product_edit_approval')){
			$sellerid = Mage::getSingleton('customer/session')->getCustomer()->getId();
			$collection = Mage::getModel('marketplace/product')->getCollection()
								->addFieldToFilter('mageproductid',array('eq'=>$id))
								->addFieldToFilter('userid',array('eq'=>$sellerid));
			foreach ($collection as $row) {
				$catarray=$loadpro->getCategoryIds();
				$categoryname='';
				$catagory_model = Mage::getModel('catalog/category');
				foreach($catarray as $keycat){
				$categoriesy = $catagory_model->load($keycat);
					if($categoryname ==''){
						$categoryname=$categoriesy->getName();
					}else{
						$categoryname=$categoryname.",".$categoriesy->getName();
					}
				}
				$allStores = Mage::app()->getStores();
				foreach ($allStores as $_eachStoreId => $val)
				{
					Mage::getModel('catalog/product_status')->updateProductStatus($id,Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
				}
				$loadpro->setStatus(2)->save();
				$row->setStatus(2);
				$row->save();
			}
			$customer = Mage::getModel('customer/customer')->load($sellerid);
			$emailTemp = Mage::getModel('core/email_template')->loadDefault('approveproduct');
			$emailTempVariables = array();
			$adminname = 'Administrators';
			$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
			$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
			$emailTempVariables['myvar1'] = $loadpro['name'];
			$emailTempVariables['myvar2'] = $categoryname;
			$emailTempVariables['myvar3'] = $adminname;
			$emailTempVariables['myvar4'] = Mage::helper('marketplace')->__('I would like to inform you that recently i have updated a product Please approve it soon.');
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			$emailTemp->setSenderName($customer->getName());
			$emailTemp->setSenderEmail($customer->getEmail());
			$emailTemp->send($adminEmail,$adminname,$emailTempVariables);
		}
		if(isset($_FILES) && count($_FILES) > 0)	{
			$cp_image_dir = Mage::getBaseDir()."/media/marketplace/";
			$this_image_path = Mage::getBaseDir()."/media/marketplace/".$id."/images/";
			$this_sample_path = Mage::getBaseDir()."/media/marketplace/".$id."/sample/";
			$this_download_path = Mage::getBaseDir()."/media/marketplace/".$id."/download/";
			$this_mainsample_path = Mage::getBaseDir()."/media/marketplace/".$id."/mainsample/";				
			if (!is_dir($cp_image_dir))
				mkdir($cp_image_dir, 0755);

			if (!is_dir($this_image_path))
				mkdir($this_image_path, 0755);

			if (!is_dir($this_sample_path))
				mkdir($this_sample_path, 0755);

			if (!is_dir($this_download_path))
				mkdir($this_download_path, 0755);
			
			if (!is_dir($this_mainsample_path))
				mkdir($this_mainsample_path, 0755);	
				
			foreach($_FILES["images"]["tmp_name"] as $key => $value){
				$splitname = explode('.', $_FILES["images"]['name'][$key]);
				$splitname[0] = str_replace('-', '', $splitname[0]);
				$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
				$target1 = $this_image_path.$image_name.".".$splitname[1];
				move_uploaded_file($value,$target1);
			}
			foreach($_FILES["sample"]["tmp_name"] as $key => $value){
				if($_FILES['sample']['tmp_name'] != '' )
					move_uploaded_file($value,$this_sample_path.$_FILES["sample"]["name"][$key]);
			}
			foreach($_FILES["wk_link"]["tmp_name"] as $key => $value){
				if($_FILES['wk_link']['tmp_name'] != '' )
					move_uploaded_file($value,$this_download_path.$_FILES["wk_link"]["name"][$key]);
			}
			foreach($_FILES["wk_samples"]["tmp_name"] as $key => $value){
				if($_FILES['wk_samples']['tmp_name'] != '' )
					move_uploaded_file($value,$this_mainsample_path.$_FILES["wk_samples"]["name"][$key]);
			}
		}

		if($wholedata['defaultimage']){
			$splitname = explode('.', $wholedata['defaultimage']);
			if($splitname[1]){
				$splitname[0] = str_replace('-', '', $splitname[0]);
				$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
				$wholedata['defaultimage'] = $image_name.".".$splitname[1]; 
			}
		}
		
		$this->AddImages($id,$wholedata['link'],$wholedata['defaultimage'],$rates,$currentCurrencyCode,$wholedata['samples']);
		if($wholedata['status']==2){
			$collection1=Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter("mageproductid",$saved->getId());
			foreach ($collection1 as $coll) {
				$coll->setStatus(2);
				$coll->save();
			}
			$allStores = Mage::app()->getStores();
			foreach ($allStores as $_eachStoreId => $val)
			{
				Mage::getModel('catalog/product_status')->updateProductStatus($saved->getId(),Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
			}
		}
		$qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($id);
		$qtyStock->setProductId($id)->setStockId(1);
		$qtyStock->setData('is_in_stock', $wholedata['is_in_stock']); 
		$savedStock = $qtyStock->save();
		if(is_dir(Mage::getBaseDir().'/media/marketplace/'.$id.'/')){
			foreach(new DirectoryIterator(Mage::getBaseDir().'/media/marketplace/'.$id.'/') as $fileInfo){
				if($fileInfo->isFile()){unlink($fileInfo->getPathname());}
			}
		}

		Mage::dispatchEvent('mp_customoption_setdata', $wholedata);

		Mage::dispatchEvent('mp_customattribute_settierpricedata', $wholedata);

		return 0;
	}
	public function editVirtualProduct($id,$wholedata){	
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

		$wholedata['id'] = $id;
		Mage::dispatchEvent('mp_customattribute_deletetierpricedata', $wholedata);

		$cats=array();
		foreach($wholedata['category'] as $keycat){
			array_push($cats,$keycat);
		}
	    $productcity = Mage::getModel('catalog/product');
		$loadpro = $productcity->load($id);
		foreach($wholedata as $key=>$val)
		{
			$loadpro->setData($key,$val);
		}
		//Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		$saved=$loadpro->save();
		$loadpro = Mage::getModel('catalog/product')->load($saved->getId());
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();		
		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
		$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
		if(!$rates[$currentCurrencyCode]){
			$rates[$currentCurrencyCode] = 1;
		}
		if($wholedata['special_price']){
			$special_price = $wholedata['special_price']/$rates[$currentCurrencyCode];
			$loadpro->setSpecialPrice($special_price);
		}
		$price = $wholedata['price']/$rates[$currentCurrencyCode];
		$loadpro->setPrice($price);
		$loadpro->setCategoryIds($cats);
		$loadpro->save();
		if(Mage::getStoreConfig('marketplace/marketplace_options/product_edit_approval')){
			$sellerid = Mage::getSingleton('customer/session')->getCustomer()->getId();
			$collection = Mage::getModel('marketplace/product')->getCollection()
								->addFieldToFilter('mageproductid',array('eq'=>$id))
								->addFieldToFilter('userid',array('eq'=>$sellerid));
			foreach ($collection as $row) {
				$catarray=$loadpro->getCategoryIds();
				$categoryname='';
				$catagory_model = Mage::getModel('catalog/category');
				foreach($catarray as $keycat){
				$categoriesy = $catagory_model->load($keycat);
					if($categoryname ==''){
						$categoryname=$categoriesy->getName();
					}else{
						$categoryname=$categoryname.",".$categoriesy->getName();
					}
				}
				$allStores = Mage::app()->getStores();
				foreach ($allStores as $_eachStoreId => $val)
				{
					Mage::getModel('catalog/product_status')->updateProductStatus($id,Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
				}
				$loadpro->setStatus(2)->save();
				$row->setStatus(2);
				$row->save();
			}
			$customer = Mage::getModel('customer/customer')->load($sellerid);
			$emailTemp = Mage::getModel('core/email_template')->loadDefault('approveproduct');
			$emailTempVariables = array();
			$adminname = 'Administrators';
			$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
			$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
			$emailTempVariables['myvar1'] = $loadpro['name'];
			$emailTempVariables['myvar2'] = $categoryname;
			$emailTempVariables['myvar3'] = $adminname;
			$emailTempVariables['myvar4'] = Mage::helper('marketplace')->__('I would like to inform you that recently i have updated a product Please approve it soon.');
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			$emailTemp->setSenderName($customer->getName());
			$emailTemp->setSenderEmail($customer->getEmail());
			$emailTemp->send($adminEmail,$adminname,$emailTempVariables);
		}
		$qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($id);
		if(isset($_FILES) && count($_FILES) > 0){
			if (!is_dir(Mage::getBaseDir().'/media/marketplace/')){
			mkdir(Mage::getBaseDir().'/media/marketplace/', 0755);
			}
			if (!is_dir(Mage::getBaseDir().'/media/marketplace/'.$wholedata['productid'])){
			mkdir(Mage::getBaseDir().'/media/marketplace/'.$wholedata['productid'], 0755);
			}
			foreach($_FILES as $image){
				$imagesdir = Mage::getBaseDir().'/media/marketplace/'.$wholedata['productid'].'/';
				$splitname = explode('.', $image['name']);
				$splitname[0] = str_replace('-', '', $splitname[0]);
				$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
				$target1 = $imagesdir.$image_name.".".$splitname[1];
				move_uploaded_file($image['tmp_name'],$target1);	
			}
				$this->_addImages($wholedata['productid']);
	    }
	    if($wholedata['status']==2){
			$collection1=Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter("mageproductid",$saved->getId());
			foreach ($collection1 as $coll) {
				$coll->setStatus(2);
				$coll->save();
			}
			$allStores = Mage::app()->getStores();
			foreach ($allStores as $_eachStoreId => $val)
			{
				Mage::getModel('catalog/product_status')->updateProductStatus($saved->getId(),Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
			}
		}
		if($wholedata['defaultimage']){
			$splitname = explode('.', $wholedata['defaultimage']);
			if($splitname[1]){
				$splitname[0] = str_replace('-', '', $splitname[0]);
				$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
				$wholedata['defaultimage'] = $image_name.".".$splitname[1]; 
			}
		}
		$this->_addImages($id,$wholedata['defaultimage']);
		$qtyStock->setProductId($id)->setStockId(1);
		$qtyStock->setData('is_in_stock', $wholedata['is_in_stock']); 
		$savedStock = $qtyStock->save();
		$qtyStock->load($savedStock->getId())->setQty($wholedata['stock'])->save();
		$qtyStock->setProductId($id)->setStockId(1);
		$qtyStock->setData('is_in_stock', $wholedata['is_in_stock']); 
		$savedStock = $qtyStock->save();
		
		Mage::dispatchEvent('mp_customoption_setdata', $wholedata);
		
		Mage::dispatchEvent('mp_customattribute_settierpricedata', $wholedata);

		return 0;
	}
	
	/* end edit*/
	public function deleteProduct($wholedata){
		$id = explode('/id/',$wholedata );
		$data['id']=$id[1];
		Mage::dispatchEvent('mp_delete_product', $data);
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
	    $collection_product = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$id[1]))->addFieldToFilter('userid',array('eq'=>$customerid));
		if(count($collection_product)) {
			Mage::register("isSecureArea", 1);
			Mage :: app("default") -> setCurrentStore( Mage_Core_Model_App :: ADMIN_STORE_ID );
			Mage::getModel('catalog/product')->load($id[1])->delete();
			$collection=Mage::getModel('marketplace/product')->getCollection()
							->addFieldToFilter('mageproductid',array('eq'=>$id[1]));
			foreach($collection as $row){
				$row->delete();
			}
			return 0;
		}
		else
		{
		return 1;
		}
	}

	public function deleteassociateProduct($wholedata){
		$id = explode('/id/',$wholedata );
		$data['id']=$id[1];
		Mage::register("isSecureArea", 1);
		Mage :: app("default") -> setCurrentStore( Mage_Core_Model_App :: ADMIN_STORE_ID );
		Mage::getModel('catalog/product')->load($id[1])->delete();
	}

	public function massdeleteProduct($id){
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
	    $collection_product = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$id))->addFieldToFilter('userid',array('eq'=>$customerid));
		if(count($collection_product)) {
			Mage::register("isSecureArea", 1);
			Mage :: app("default") -> setCurrentStore( Mage_Core_Model_App :: ADMIN_STORE_ID );
			Mage::getModel('catalog/product')->load($id)->delete();
			$collection=Mage::getModel('marketplace/product')->getCollection()
							->addFieldToFilter('mageproductid',array('eq'=>$id));
			foreach($collection as $row){
				$row->delete();
			}
			return 0;
		}
		else
		{
		return 1;
		}
	}
	
	private function _saveStock($lastId,$stock,$isstock){
		$stockItem = Mage::getModel('cataloginventory/stock_item');
		$stockItem->loadByProduct($lastId);
		if(!$stockItem->getId()){$stockItem->setProductId($lastId)->setStockId(1);}
		$stockItem->setProductId($lastId)->setStockId(1);
		$stockItem->setData('is_in_stock', $isstock); 
		$savedStock = $stockItem->save();
		$stockItem->load($savedStock->getId())->setQty($stock)->save();
		// $qtyStock->setProductId($lastId)->setStockId(1);
		$stockItem->setData('is_in_stock', $isstock); 
		$savedStock = $stockItem->save();
	}
	private function _addImages($objProduct,$defaultimage){
		$mediDir = Mage::getBaseDir('media');
		$imagesdir = $mediDir . '/marketplace/' . $objProduct . '/';
		if(!file_exists($imagesdir)){return false;}
		foreach (new DirectoryIterator($imagesdir) as $fileInfo){
    		if($fileInfo->isDot() || $fileInfo->isDir()) continue;
    		if($fileInfo->isFile()){
    			$file_extension = pathinfo($fileInfo->getPathname(), PATHINFO_EXTENSION);
				$allow_extension = array('png','gif','jpg','jpeg');
				if(in_array($file_extension,$allow_extension)){
					$fileinfo=explode('@',$fileInfo->getPathname());
					$objprod=Mage::getModel('catalog/product')->load($objProduct);
					$objprod->addImageToMediaGallery($fileInfo->getPathname(), array ('image','small_image','thumbnail'), true, false);
					Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
					$objprod->save();					
				}
    		}
		}
		$_product = Mage::getModel('catalog/product')->load($objProduct)->getMediaGalleryImages();
		if (strpos($defaultimage, '.') !== FALSE){
			$defimage =  explode('.',$defaultimage);
			$defimage[0] = str_replace('-', '_', $defimage[0]);
			foreach ($_product as $value) {
				$image = explode($defimage[0],$value->getFile());
				if (strpos($value->getFile(), $defimage[0]) !== FALSE){
					$newimage = $value->getFile();
				}
			}
		}else{
			foreach ($_product as $value) {
				if($value->getValueId()==$defaultimage){
					$newimage = $value->getFile();
				}
			}
		}
		$objprod=Mage::getModel('catalog/product')->load($objProduct);
		$objprod->setSmallImage($newimage);
		$objprod->setImage($newimage);
		$objprod->setThumbnail($newimage);
		$objprod->save();	
		//die();
	}

	private function _addImagesMulti($objProduct,$defaultimage,$uploadImg){
		$mediDir = Mage::getBaseDir('media');
		$imagesdir = $mediDir . '/marketplace/' . $objProduct . '/';
		//if(!file_exists($imagesdir)){return false;}
		foreach ($uploadImg as $fileInfo ){
					$objprod=Mage::getModel('catalog/product')->load($objProduct);
					$objprod->addImageToMediaGallery($fileInfo, array ('image','small_image','thumbnail'), false, false);
					Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
					$objprod->save();
		}
		$_product = Mage::getModel('catalog/product')->load($objProduct)->getMediaGalleryImages();
		//var_dump($uploadImg,$_product->getItems());die;
		if (strpos($defaultimage, '.') !== FALSE){
			$defimage =  explode('.',$defaultimage);
			$defimage[0] = str_replace('-', '_', $defimage[0]);
			foreach ($_product as $value) {
				if (strpos($value->getFile(), $defimage[0]) !== FALSE){
					$newimage = $value->getFile();
				}
			}
		}else{
            foreach ($_product as $value) {
                if($value->getValueId()==$defaultimage){
                    $newimage = $value->getFile();
                }
            }
        }

//        var_dump($defaultimage,$newimage );

		$objprod=Mage::getModel('catalog/product')->load($objProduct);
		$objprod->setSmallImage($newimage);
		$objprod->setImage($newimage);
		$objprod->setThumbnail($newimage);
		$objprod->save();
		//die();
	}
	private function AddImages($id,$wholedata,$defaultimage,$rates,$currentCurrencyCode,$mainsample)	{
			$mediDir = Mage::getBaseDir("media");
			$imagesdir = $mediDir."/marketplace/".$id."/images/";
			foreach(new DirectoryIterator($imagesdir) as $fileInfo){
	    		if($fileInfo->isDot() || $fileInfo->isDir())
	    			continue;
	    		if($fileInfo->isFile())	{
	    			$file_extension = pathinfo($fileInfo->getPathname(), PATHINFO_EXTENSION);
					$allow_extension = array('png','gif','jpg','jpeg');
					if(in_array($file_extension,$allow_extension)){
						$product = Mage::getModel("catalog/product")->load($id);
						$product->addImageToMediaGallery($fileInfo->getPathname(), array ("image","small_image","thumbnail"), true, false);
						Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
						$product->save();
					}
				}
			}
			$download_dir = Mage::getBaseDir()."/media/downloadable/files/";
			$link_samples_path = Mage::getBaseDir()."/media/downloadable/files/link_samples/";
			$links_path = Mage::getBaseDir()."/media/downloadable/files/links/";	
			$samples_path = Mage::getBaseDir()."/media/downloadable/files/samples/";	
			if (!is_dir($download_dir))
				mkdir($download_dir, 0755);

			if (!is_dir($link_samples_path))
				mkdir($link_samples_path, 0755);

			if (!is_dir($links_path))
				mkdir($links_path, 0755);
				
			if (!is_dir($samples_path))
				mkdir($samples_path, 0755);	

			$sampledir = $mediDir."/marketplace/".$id."/sample/";
			$downloaddir = $mediDir."/marketplace/".$id."/download/";
			$mainsampledir = $mediDir."/marketplace/".$id."/mainsample/";
			$sample_allow_extensions = Mage::getStoreConfig('marketplace/marketplace_options/samplefiletype',Mage::app()->getStore()->getId());
			$sample_allow_extension = explode(',',$sample_allow_extensions);
			$samplefile_extensions = $sample_allow_extension;
			$link_allow_extensions = Mage::getStoreConfig('marketplace/marketplace_options/downloadfiletype',Mage::app()->getStore()->getId());
			$link_allow_extension = explode(',',$link_allow_extensions);	
			$downloadfile_extensions = $link_allow_extension;
			if(!file_exists($sampledir)){return false;}
			if(!file_exists($downloaddir)){return false;}
			$i=0;
			$savelinkserver = Mage::getStoreConfig('marketplace/marketplace_options/savelinkserver',Mage::app()->getStore()->getId());
			foreach($wholedata as $row)	{
				$wholedata[$i]['price'] = $wholedata[$i]['price']/$rates[$currentCurrencyCode];
				if($wholedata[$i]['wk_link_id']!='')	{
					$linkModel = Mage::getModel('downloadable/link')->load($wholedata[$i]['wk_link_id']);
					$web_id=Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)->getWebsiteId().",".Mage::app()->getStore()->getId();
					$linkModel->setWebsiteIds(array($web_id));
					$linkModel ->setPrice($wholedata[$i]['price']);
					$linkModel->setTitle($wholedata[$i]['title']);
					if($wholedata[$i]['is_unlimited']!=1)
						$linkModel->setNumberOfDownloads($wholedata[$i]['n_of_d']);
					else
						$linkModel->setNumberOfDownloads(0);
					if($_FILES["wk_link"]["name"][$i]!='')	{
						foreach (new DirectoryIterator($downloaddir) as $fileInfo)	{
							if($fileInfo->isDot() || $fileInfo->isDir()) continue;
							if($fileInfo->isFile())	{
								$filename = $fileInfo->getFilename();
								$path_parts = pathinfo($fileInfo->getPathname());
								$fileext= $path_parts['extension'];
								if(in_array(strtolower($fileext), $downloadfile_extensions)){
									if($path_parts['basename']==$_FILES["wk_link"]["name"][$i]){
										$newfilename=time().$_FILES["wk_link"]["name"][$i];
										Mage::dispatchEvent('wk_mp_downloadable_link_add',array(
											'filename' 	=> $newfilename,
											'filepath' 	=> $fileInfo->getPathname(),
											'linkmodel' => $linkModel,
											)
										);
										if($savelinkserver != 2){
											$newfile1=Mage::getBaseDir('media').'/downloadable/files/links/'.$newfilename;
											copy($fileInfo->getPathname(), $newfile1);											
											$linkModel->setLinkFile("/".$newfilename);
											$linkModel->setLinkType("file");
										}
										unlink($fileInfo->getPathname());
									}
								}
							}
						}
					}
					if($_FILES["sample"]["name"][$i]!='' )	{
						foreach (new DirectoryIterator($sampledir) as $fileInfo){
							if($fileInfo->isDot() || $fileInfo->isDir()) continue;
							if($fileInfo->isFile()){
								$filename = $fileInfo->getFilename();
								$path_parts = pathinfo($fileInfo->getPathname());
								$fileext= $path_parts['extension'];
								if(in_array(strtolower($fileext), $samplefile_extensions)){
									if($path_parts['basename']==$_FILES["sample"]["name"][$i]){
										$newfilename=time().$_FILES["sample"]["name"][$i];
										$newfile1=Mage::getBaseDir('media').'/downloadable/files/link_samples/'.$newfilename;
										copy($fileInfo->getPathname(), $newfile1);
										unlink($fileInfo->getPathname());
										$linkModel->setSampleFile("/".$newfilename);
										$linkModel->setSampleType("file");
									}
								}
							}
						}
					}
					$linkModel->save();
				}
				else if($wholedata[$i]['wk_link_id']=='')	{
					$linkModel = Mage::getModel('downloadable/link');
					foreach (new DirectoryIterator($sampledir) as $fileInfo){
							if($fileInfo->isDot() || $fileInfo->isDir()) continue;
							if($fileInfo->isFile()){
								$filename = $fileInfo->getFilename();
								$path_parts = pathinfo($fileInfo->getPathname());
								$fileext= $path_parts['extension'];
								if(in_array(strtolower($fileext), $samplefile_extensions)){
									if($path_parts['basename']==$_FILES["sample"]["name"][$i]){
										$newfilename=time().$_FILES["sample"]["name"][$i];
										$newfile1=Mage::getBaseDir('media').'/downloadable/files/link_samples/'.$newfilename;
										copy($fileInfo->getPathname(), $newfile1);
										unlink($fileInfo->getPathname());
										$linkModel->setSampleFile("/".$newfilename);
										$linkModel->setSampleType("file");
									}
								}
							}
						}
						foreach (new DirectoryIterator($downloaddir) as $fileInfo){
							if($fileInfo->isDot() || $fileInfo->isDir()) continue;
							if($fileInfo->isFile()){
								$filename = $fileInfo->getFilename();
								$path_parts = pathinfo($fileInfo->getPathname());
								$fileext= $path_parts['extension'];
								if(in_array(strtolower($fileext), $downloadfile_extensions)){
									if($path_parts['basename']==$_FILES["wk_link"]["name"][$i]){
										$newfilename=time().$_FILES["wk_link"]["name"][$i];
										$newfile1=Mage::getBaseDir('media').'/downloadable/files/links/'.$newfilename;
										Mage::dispatchEvent('wk_mp_downloadable_link_add',array(
											'filename' 	=> $newfilename,
											'filepath' 	=> $fileInfo->getPathname(),
											'linkmodel' => $linkModel,
											)
										);
										if($savelinkserver != 2){
											copy($fileInfo->getPathname(), $newfile1);											
											$linkModel->setLinkFile("/".$newfilename);
											$linkModel->setLinkType("file");
										}
										unlink($fileInfo->getPathname());
									}
								}
							}
						}
						$linkModel->setProductId($id);
						$linkModel->setStoreId(0);
						$web_id=Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)->getWebsiteId().",".Mage::app()->getStore()->getId();
						$linkModel->setWebsiteIds(array($web_id));
						$linkModel->setPrice($wholedata[$i]['price']);
						$linkModel->settitle($wholedata[$i]['title']);
						if($wholedata[$i]['is_unlimited']!=1)
							$linkModel->setNumberOfDownloads($wholedata[$i]['n_of_d']);
						else
							$linkModel->setNumberOfDownloads(0);
						$linkModel->save();
				}
				$i++;
			}
			
			if(!file_exists($mainsampledir)){return false;}
			$j=0;
			foreach($mainsample as $keys){
			   if($mainsample[$j]['wk_sample_id']!='')	{
					$sampleModel = Mage::getModel('downloadable/sample')->load($mainsample[$j]['wk_sample_id']);
					$web_id=Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)->getWebsiteId().",".Mage::app()->getStore()->getId();
					$sampleModel->setWebsiteIds(array($web_id));
					$sampleModel->setTitle($mainsample[$j]['title']);
					if($_FILES["wk_samples"]["name"][$j]!='')	{
						foreach (new DirectoryIterator($mainsampledir) as $fileInfo)	{
							if($fileInfo->isDot() || $fileInfo->isDir()) continue;
							if($fileInfo->isFile())	{
								$filename = $fileInfo->getFilename();
								$path_parts = pathinfo($fileInfo->getPathname());
								$fileext= $path_parts['extension'];
								if(in_array(strtolower($fileext), $samplefile_extensions)){
									if($path_parts['basename']==$_FILES["wk_samples"]["name"][$j]){
										$newfilename=time().$_FILES["wk_samples"]["name"][$j];
										$newfile1=Mage::getBaseDir('media').'/downloadable/files/samples/'.$newfilename;
										copy($fileInfo->getPathname(), $newfile1);
										unlink($fileInfo->getPathname());
										$sampleModel->setSampleFile("/".$newfilename);
										$sampleModel->setSampleType("file");
									}
								}
							}
						}
					}
					$sampleModel->save();
				}
				else if($mainsample[$j]['wk_sample_id']=='')	{
					$sampleModel = Mage::getModel('downloadable/sample');
						foreach (new DirectoryIterator($mainsampledir) as $fileInfo){
							if($fileInfo->isDot() || $fileInfo->isDir()) continue;
							if($fileInfo->isFile()){
								$filename = $fileInfo->getFilename();
								$path_parts = pathinfo($fileInfo->getPathname());
								$fileext= $path_parts['extension'];
								if(in_array(strtolower($fileext), $samplefile_extensions)){
									if($path_parts['basename']==$_FILES["wk_samples"]["name"][$j]){
										$newfilename=time().$_FILES["wk_samples"]["name"][$j];
										$newfile1=Mage::getBaseDir('media').'/downloadable/files/samples/'.$newfilename;
										copy($fileInfo->getPathname(), $newfile1);
										unlink($fileInfo->getPathname());
										$sampleModel->setSampleFile("/".$newfilename);
										$sampleModel->setSampleType("file");
									}
								}
							}
						}
						$sampleModel->setProductId($id);
						$sampleModel->setStoreId(0);
						$web_id=Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)->getWebsiteId().",".Mage::app()->getStore()->getId();
						$sampleModel->setWebsiteIds(array($web_id));
						//$sampleModel->setPrice($wholedata[$i]['price']);
						$sampleModel->settitle($mainsample[$j]['title']);
						$sampleModel->save();
				}
			   $j++;
			}		
				
			$_product = Mage::getModel('catalog/product')->load($id)->getMediaGalleryImages();
			if (strpos($defaultimage, '.') !== FALSE){
				$defimage =  explode('.',$defaultimage);
				//$defimagechange  = str_replace('-', '_', $defimage[0]);
				foreach ($_product as $value) {
					$image = explode($defimage[0],$value->getFile());
					if (strpos($value->getFile(), $defimage[0]) !== FALSE || strpos($value->getFile(), $defimagechange) !== FALSE){
						$newimage = $value->getFile();
					}
				}
			}else{
				foreach ($_product as $value) {
					if($value->getValueId()==$defaultimage){
						$newimage = $value->getFile();
					}
				}
			}
			$objprod=Mage::getModel('catalog/product')->load($id);
			$objprod->setSmallImage($newimage);
			$objprod->setImage($newimage);
			$objprod->setThumbnail($newimage);
			$objprod->save(); 
			//die;
	}	
	
	public function approveSimpleProduct($id){
		$magentoProductModel = Mage::getModel('catalog/product')->load($id);
		$catarray=$magentoProductModel->getCategoryIds();
		$categoryname='';
		$catagory_model = Mage::getModel('catalog/category');
		foreach($catarray as $keycat){
		$categoriesy = $catagory_model->load($keycat);
			if($categoryname ==''){
				$categoryname=$categoriesy->getName();
			}else{
				$categoryname=$categoryname.",".$categoriesy->getName();
			}
		}
		$users = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$id));
		foreach ($users as $value) {
			$user = $value;
		}
		$allStores = Mage::app()->getStores();
		foreach ($allStores as $_eachStoreId => $val)
		{
			Mage::getModel('catalog/product_status')->updateProductStatus($id,Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
		}
		
		$magentoProductModel->setStatus(1);
		$saved=$magentoProductModel->save();
		$lastId = $saved->getId();
		$pro=Mage::getModel('marketplace/product')->load($user->getIndexId());
		$pro->setStatus(1);
		$pro->save();	
		$adminname='Administrator';
		$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
		$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
		$seller = Mage::getModel('customer/customer')->load($user->getUserid());
		$headers = "From: STORE";
		$emailTemp = Mage::getModel('core/email_template')->loadDefault('whenproductapproved');
		$emailTempVariables = array();
		$emailTempVariables['myvar1'] = $magentoProductModel->getName();
		$emailTempVariables['myvar2'] =$magentoProductModel->getDescription();
		$emailTempVariables['myvar3'] =$magentoProductModel->getPrice();
		$emailTempVariables['myvar4'] =$categoryname;
		$emailTempVariables['myvar5'] =$seller->getname();
		$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
		$emailTemp->setSenderName($adminname);
		$emailTemp->setSenderEmail($adminEmail); 
		$emailTemp->send($seller->getemail(),$seller->getname(),$emailTempVariables);
		//For product approval
		Mage::dispatchEvent('mp_approve_product',array('product'=>$pro,'seller'=>$seller));
		return $lastId;
	}
	
	public function isCustomerProduct($magentoProductId){
		$collection = Mage::getModel('marketplace/product')->getCollection();
		$collection->addFieldToFilter('mageproductid',array($magentoProductId));
		$userid='';
		$status='';
		foreach($collection as $record){
		$userid=$record->getuserid();
		}
		$collection1 = Mage::getModel('marketplace/userprofile')->getCollection()->addFieldToFilter('mageuserid',array('eq'=>$userid));
		foreach($collection1 as $record1){
		$status=$record1->getWantpartner();
		}
		if($status!=1){
			$userid='';
		}
		
		return array('productid'=>$magentoProductId,'userid'=>$userid);
	}	
}
