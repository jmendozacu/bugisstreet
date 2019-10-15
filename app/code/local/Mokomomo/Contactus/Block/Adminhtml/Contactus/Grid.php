<?php

class Mokomomo_Contactus_Block_Adminhtml_Contactus_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('contactusGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('contactus_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('contactus/contactus')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('contactus_id', array(
                            'header'    => 'ID',
                            'align'     => 'right',
                            'width'     => '50px',
                            'index'     => 'contactus_id',
                        ));

        $this->addColumn('field_name', array(
                            'header'    => 'Field Name',
                            'align'     => 'left',
                            'index'     => 'field_name',
                        ));
                        
        $this->addColumn('match_value', array(
                            'header'    => 'Match Value',
                            'align'     => 'left',
                            'index'     => 'match_value',
                        ));

        $this->addColumn('email', array(
                            'header'    => 'E-Mail',
                            'align'     => 'left',
                            'index'     => 'email',
                        ));
                        
        $this->addColumn('delete',
                            array(
                                'header'    => 'Action',
                                'width'     => '50px',
                                'type'      => 'action',
                                'getter'    => 'getId',
                                'actions'   => array(
                                   array('caption' => 'Delete', 'url' => array('base'=> '*/*/delete'), 'field' => 'id'),
                                ),
                                'filter'    => false,
                                'sortable'  => false,
                                'is_system' => true,
                           ));

        return parent::_prepareColumns();
    }



}