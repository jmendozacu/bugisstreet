<?php

class Webkul_Marketplace_Model_Producttypemp
{
   public function toOptionArray(){
            $data =  array(array('value'=>'simple', 'label'=>'Simple'),
							array('value'=>'downloadable', 'label'=>'Downloadable'),
							array('value'=>'virtual', 'label'=>'Virual'),
							array('value'=>'configurable', 'label'=>'Configurable')
			);
			if(Mage::helper('core')->isModuleEnabled('Webkul_Mpbundleproduct')){
				array_push($data,array('value'=>'bundle', 'label'=>'Bundle Product'));
			}
			if(Mage::helper('core')->isModuleEnabled('Webkul_Mpgroupproduct')){
				array_push($data,array('value'=>'grouped', 'label'=>'Grouped Product'));
			}
		return  $data;                

    }
}
