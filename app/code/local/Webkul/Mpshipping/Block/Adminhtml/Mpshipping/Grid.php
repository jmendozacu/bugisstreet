<?php

class Webkul_Mpshipping_Block_Adminhtml_Mpshipping_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('mpshippingGrid');
      $this->setDefaultSort('mpshipping_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('mpshipping/mpshipping')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('mpshipping_id', array(
          'header'    => Mage::helper('mpshipping')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'mpshipping_id',
      ));

      $this->addColumn('dest_country_id', array(
          'header'    => Mage::helper('mpshipping')->__('Country Id'),
          'align'     =>'left',
          'index'     => 'dest_country_id',
      ));
	   $this->addColumn('dest_region_id', array(
          'header'    => Mage::helper('mpshipping')->__('Region Id'),
          'align'     =>'left',
          'index'     => 'dest_region_id',
      ));
     $this->addColumn('dest_zip', array(
          'header'    => Mage::helper('mpshipping')->__('ZIP From'),
          'align'     =>'left',
          'index'     => 'dest_zip',
      ));
	   $this->addColumn('dest_zip_to', array(
          'header'    => Mage::helper('mpshipping')->__('ZIP To'),
          'align'     =>'left',
          'index'     => 'dest_zip_to',
      ));
	   $this->addColumn('price', array(
          'header'    => Mage::helper('mpshipping')->__('Price'),
          'align'     =>'left',
          'index'     => 'price',
      ));
	  $this->addColumn('weight_from', array(
          'header'    => Mage::helper('mpshipping')->__('Weight From'),
          'align'     =>'left',
          'index'     => 'weight_from',
      ));
	    $this->addColumn('weight_to', array(
          'header'    => Mage::helper('mpshipping')->__('Weight To'),
          'align'     =>'left',
          'index'     => 'weight_to',
      ));

		
		$this->addExportType('*/*/exportCsv', Mage::helper('mpshipping')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('mpshipping')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('mpshipping_id');
        $this->getMassactionBlock()->setFormFieldName('mpshipping');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('mpshipping')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('mpshipping')->__('Are you sure?')
        ));
        
        return $this;
    }

  /*public function getRowUrl($row)
  {
      return $this->getUrl('*//*/edit', array('id' => $row->getId()));
  }*/

}
