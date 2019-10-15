<?php

class MW_Publicbank_Model_Standard_PaymentAction
{
	public function toOptionArray()
	{
		return array(
			//array(
			//	'value' => 'authorize_capture',
			//	'label' => 'Authorise and Capture'
			//),
			array(
				'value' => '',
				'label' => 'Authorise'
			)
		);
	}
}

?>
