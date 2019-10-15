<?php 
class Webkul_Mpadvancecommission_Model_Commissiontype
{
    public function toOptionArray()
    {
		$data=array(
				array('value'=>'fixed','label'=>'Fixed'),
				array('value'=>'percent','label'=>'Percentage'),
				
			);
        return  $data;                
    }
  } 
