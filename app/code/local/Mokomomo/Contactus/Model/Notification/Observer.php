<?php
class Mokomomo_Contactus_Model_Notification_Observer
{
    public function __construct()
    {
    }
    
    public function SendNotification($observer)
    {
        $event = $observer->getEvent();
        $data = $event->getData();

        $result = $data['result'];
        $webform = $data['webform'];
        if (!Mage::registry('webform'))
			Mage::register('webform', $webform);

        $resultModel = Mage::getModel('webforms/results')->load($result->getId());
        $postData = Mage::app()->getRequest()->getPost();
        $email = "";

        $subject = $resultModel->getEmailSubject('admin');
        //$sender = Array ('email' => $resultModel->getReplyTo('admin'), 'name' => $resultModel->getReplyTo('admin'));
        $sender = Array ('email' => Mage::getStoreConfig('trans_email/ident_general/email'), 'name' => Mage::getStoreConfig('trans_email/ident_general/name'));
        $name = Mage::app()->getStore($resultModel->getStoreId())->getFrontendName();
		$storeId = Mage::app()->getStore($resultModel->getStoreId())->getId();
		$templateId = 'webforms_notification';
        if ($webform->getEmailTemplateId()) { $templateId = $webform->getEmailTemplateId(); }

        $vars = Array
		(
			'webform_subject' => $subject,
			'webform_name' => $webform->getName(),
			'webform_result' => $resultModel->toHtml('admin'),
			'result' => $resultModel->getTemplateResultVar(),
		);
        $vars['noreply'] = Mage::helper('webforms')->__('Please, don`t reply to this e-mail!');
        
        foreach ($webform->getFieldsToFieldsets() as $fieldset_id => $fieldset)
        {
            foreach($fieldset['fields'] as $idx => $field)
            {
                $filter = $webform['name'].' - '. $fieldset['name'].' - '.$field['name'];
                $models = Mage::getModel('contactus/contactus')->getCollection()->addFilter('Field_Name', $filter)->getData();
                if(is_array($models) && count($models)>0)
                {
                    $matchValue = $postData['field'][$field['id']];
                    if($matchValue!="")
                    {
                        foreach($models as $model)
                        {
                            if($matchValue == $model['match_value'])
                            {
                                $email .= ($email?",":"").$model['email'];
                            }
                        }
                    }
                }
                if($email!="") break;
            }
            if($email!="") break;
        }
        
        if($email !="")
        {
            if (strstr($email, ','))
            {
                $email_array = explode(',', $email);
                foreach ($email_array as $email) 
                { 
                    $success = Mage::getModel('core/email_template')
                                ->setTemplateSubject($subject)
                                ->sendTransactional($templateId, $sender, $email, $name, $vars);
                }
            }
            else 
            {
                $success = Mage::getModel('core/email_template')
                    ->setTemplateSubject($subject)
                    ->sendTransactional($templateId, $sender, $email, $name, $vars);
            }
        }
    }
    
    
}

?>
