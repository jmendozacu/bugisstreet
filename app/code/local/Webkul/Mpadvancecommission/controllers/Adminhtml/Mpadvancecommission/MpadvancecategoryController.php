<?php
class Webkul_Mpadvancecommission_Adminhtml_Mpadvancecommission_MpadvancecategoryController extends Mage_Adminhtml_Controller_Action
{
	
	 
	 public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
      }

     public function savecategorypercentAction(){
		$data = $this->getRequest()->getParams();
		$arr=array();
		$allcat=array();
		
			 foreach($data['objct'] as $ky){
				
			  foreach($ky as $key=>$val)
			  {
				   $categ = Mage::getModel('catalog/category');
				   $cat = $categ->load($key);  
				   $cat->setCommissionForAdmin($val); 
				   $cat->save();  
			  }
			 }
		
		
		 echo json_encode(true);
	}
	
	
	
	public function categorytreeAction(){
		$data = $this->getRequest()->getParams();
		$category_model = Mage::getModel("catalog/category");
		$category = $category_model->load($data["CID"]);
		$children = $category->getChildren();
		$categories=array();
		$catt='';
		$all = explode(",",$children);$result_tree = "";$ml = $data["ML"]+20;$count = 1;$total = count($all);
		$plus = 0;
		
		foreach($all as $each){
			$count++;
			$_category = $category_model->load($each);
			
			if(count($category_model->getResource()->getAllChildren($category_model->load($each)))-1 > 0){
			    $result[$plus]['counting']=1;
				$result[$plus]['id']= $_category['entity_id'];
				$result[$plus]['name']= $_category->getName();
		        $result[$plus]['comisn'] = Mage::getModel('catalog/category')->load($_category['entity_id'])->getCommissionForAdmin();	
			}
			else
			$result[$plus]['counting']=0;
			$result[$plus]['id']= $_category['entity_id'];
		    $result[$plus]['name']= $_category->getName();
		    if($comms= Mage::getModel('catalog/category')->load($_category['entity_id'])->getCommissionForAdmin()!='')
		       $result[$plus]['comisn'] = Mage::getModel('catalog/category')->load($_category['entity_id'])->getCommissionForAdmin();
			else
			    $result[$plus]['comisn'] = '';
			if(array_key_exists('CATS', $data)){	
			$categories = explode(",",$data["CATS"]);
			$catt=$data["CATS"];
		    }
			if($catt && in_array($_category["entity_id"],$categories))
			{
			 $result[$plus]['check']= 1;
			}
			else
			{
		     $result[$plus]['check']= 0;
			}
			$plus++;
		}
		echo json_encode($result);
	}
	
	
}


