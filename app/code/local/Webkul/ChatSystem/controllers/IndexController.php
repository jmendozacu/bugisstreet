<?php
class Webkul_ChatSystem_IndexController extends Mage_Core_Controller_Front_Action	{

    public function saveconversationAction(){
		$wholedata = $this->getRequest()->getParams();		
		$model = Mage::getModel("chatsystem/conversation");
		$model->setFromname($wholedata["fromname"]);
		$model->setForid($wholedata["forid"]);	
		$model->setToname($wholedata["toname"]);
		$model->setToid($wholedata["toid"]);
		$model->setMessage($wholedata["message"]);
		$model->setCreatedAt(time());
		$model->save();
		$admin_notify_coll = Mage::getModel("chatsystem/usernotify")->getCollection()->addFieldToFilter("userid",$wholedata["forid"])->addFieldToFilter("touserid",$wholedata["toid"])->getData();
		if($admin_notify_coll[0]["id"] > 0){
			$model = Mage::getModel("chatsystem/usernotify")->load($admin_notify_coll[0]["id"]);
			$model->setUserid($wholedata["forid"]);
			$model->setTouserid($wholedata["toid"]);
			$model->setStatus(1);
			$model->save();
		}
		else{
			$model = Mage::getModel("chatsystem/usernotify");
			$model->setUserid($wholedata["forid"]);
			$model->setTouserid($wholedata["toid"]);
			$model->setStatus(1);        
			$model->save();
		}			
    }
	
	public function checkifnewusermessagethereAction(){
		$admin_notify_coll = Mage::getModel("chatsystem/usernotify")->getCollection()
		->addFieldToFilter("touserid",Mage::getSingleton('customer/session')->getCustomer()->getId())
		->addFieldToFilter("userid",array("neq"=>0))
		->addFieldToFilter("status",1);
		$arrayName;
		foreach ($admin_notify_coll as $value)  {
			$customer 	= Mage::getModel("customer/customer")->load($value->getUserid());
			$arrayName .= $customer->getName()."-";
			$arrayName .= $value->getUserid().",";
		}
		echo $arrayName;
	}

    public function notifyusermsgisreadnowAction(){
      $admin_notify_coll = Mage::getModel("chatsystem/usernotify")->getCollection()
	  ->addFieldToFilter("userid",$this->getRequest()->getParam("toid"))
	  ->addFieldToFilter("touserid",$this->getRequest()->getParam("userid"))
	  ->getData();
      $model = Mage::getModel("chatsystem/usernotify")->load($admin_notify_coll[0]["id"]);
      $model->setStatus(0);
      $model->save();
    } 

    public function fetchmsgforadminfromusersAction(){
		$wholedata = $this->getrequest()->getParams();
		$collection = Mage::getModel("chatsystem/conversation")->getCollection();
		if($wholedata["toid"]!='admin'){
			$collection->getSelect()->where('(forid ='.$wholedata["id"].' AND toid ='.$wholedata["toid"].') OR (forid ='.$wholedata["toid"].' AND toid ='.$wholedata["id"].')')->order('id DESC')->limit($wholedata["limit"]);
		}
		$fullconversation = array();
		foreach ($collection as $value)
			array_push($fullconversation,array("from" => $value->getFromname(),"message" => $value->getMessage()));
		echo json_encode($fullconversation);
    }
    
}