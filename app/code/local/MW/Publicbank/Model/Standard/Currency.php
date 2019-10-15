<?php

class MW_Publicbank_Model_Standard_Currency
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'base',
                'label' => 'Use Base Currency'
            ),
            array(
                'value' => 'display',
                'label' => 'Use Display Currency'
            ),
        );
    }
}

?>
