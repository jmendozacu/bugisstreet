<?php

class Mokomomo_Contactus_Block_Adminhtml_Contactus_New extends Mage_Adminhtml_Block_Widget_Form
{

    protected $fieldselection;
    
    protected function _prepareLayout()
    {
        if(isset($_REQUEST['back']) && $_REQUEST['back'] == "back")
        {
            Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index")); 
            return;
        }
        if(isset($_REQUEST['save']) && $_REQUEST['save'] == "save")
        {
            try{
                //save at here
                $field = filter_var($_REQUEST['field'], FILTER_SANITIZE_STRING);
                $matchValue = filter_var($_REQUEST['matchValue'], FILTER_SANITIZE_STRING);
                $email = filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL);

                if($field!="" && $matchValue!="" && $email!="")
                {
                    $model = Mage::getModel('contactus/contactus');
                    $model->setFieldName($field);
                    $model->setMatchValue($matchValue);
                    $model->setEmail($email);
                    $model->save();
                    Mage::getSingleton('adminhtml/session')->addSuccess('E-Mail Added');
                }
                else
                {
                    Mage::getSingleton('adminhtml/session')->addError('All fields must be filled');
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError('Failed to add E-Mail. Error:'.$e->getMessage());
            }
            Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index")); 
        }
        
        $this->fieldselection = array();
        $webforms = Mage::getModel('webforms/webforms')->getCollection()->getData();
        foreach ($webforms as $webform){	
            $fieldsets = Mage::getModel('webforms/webforms')->load($webform['id'])->getFieldsToFieldsets();
            foreach ($fieldsets as $fieldset){	
                foreach($fieldset['fields'] as $field)
                {
                    $this->fieldselection[] =$webform['name'].' - '. $fieldset['name'].' - '.$field['name'];
                }
            }
        }
        parent::_prepareLayout();
        
    }

}