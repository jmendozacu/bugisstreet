<?php
class Webkul_Marketplace_Block_Adminhtml_Partners_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		  parent::__construct();
		  $this->setId('marketplaceGrid');
		  $this->setDefaultSort('autoid');
		  $this->setDefaultDir('ASC');
		  $this->setSaveParametersInSession(true);
		  $this->setUseAjax(false);
          $this->setVarNameFilter('product_filter');
	}

	protected function _prepareCollection(){
		$collectionfetch = Mage::getModel('marketplace/userprofile')->getCollection();
		$record = array();
		foreach($collectionfetch as $id)
		{
			$record[] = $id->getmageuserid();
		}
		if(count($record)!=0){	
			$collection = Mage::getModel('customer/customer')->getCollection()
				->addNameToSelect()
				->addAttributeToSelect('email')
				->addAttributeToSelect('created_at')
				->addAttributeToSelect('group_id')
				->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
				->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
				->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
				->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
				->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
			$collection->addAttributeToFilter('entity_id', array('in' => $record));
			$collection->joinTable('marketplace/userprofile', 'mageuserid=entity_id', array('partnerstatus' => 'partnerstatus'), null, 'left');
            //$collection->joinLeft(array('userprofile'=>$collection->getTable('marketplace/userprofile')), 'userprofile.mageuserid=main_table.entity_id',array('partnerstatus'=>'partnerstatus'));
			$this->setCollection($collection);
		}else{
            $collectionfetch->addFieldToFilter('mageuserid', array('eq' => -1));
            $this->setCollection($collectionfetch);
        }
        parent::_prepareCollection();  
		if(count($record)!=0){
			foreach ($collection as $data) {
				/*$data->deny = sprintf('<button type="button" class="wk_denyseller" customer-id="%s"><span><span title="Deny">Deny</span></span></button>',$data->getmageuserid());*/
                $data->deny = 'Deny';
				$isThisEnabled = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Sellerstatus');
				if($isThisEnabled == 1){
					$data->sellerstatus = sprintf('<span class="status" data="'.$this->getUrl('sellerstatus/adminhtml_sellerstatus/sellerstatus').'" value="'.$data->getId().'"><button>Download Status</button></span>');
				}
				/*$data->name=sprintf('<a href="%s" title="View Customer">%s</a>',
												 $this->getUrl("adminhtml/customer/edit",array("id"=>$data->getEntityId())),$data->getName());
                $data->order=sprintf('<a href="%s" title="View Order">Order</a>',
												 $this->getUrl('marketplace/adminhtml_order/index/id/'.$data->getEntityId().'/'));*/
                $data->order = 'Order';
				$ispartnerEnabled = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Mppartnergroup');
				if($ispartnerEnabled == 1){
						$groups=Mage::getModel('mppartnergroup/assinegroup')
											->getCollection()
											->addFieldToFilter('partner_id',array('eq'=>$data->getId()));
						if(count($groups) && $data->getPartnerstatus()=='Seller'){
							foreach($groups as $group){ $data->group=$group->getType();}
						}else{$data->group='Unassined';}
				}
			}
		}
    }
	
	protected function _prepareColumns(){
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name',
            'type'      => 'text',
        ));
        $this->addColumn('email', array(
            'header'    => Mage::helper('customer')->__('Email'),
            'width'     => '150',
            'index'     => 'email',
        ));
		$this->addColumn('partnerstatus', array(
            'header'    => Mage::helper('customer')->__('Seller Status'),
            'index'     => 'partnerstatus',
            'filter_index' => 'partnerstatus',
        ));
		$ispartnerEnabled = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Mppartnergroup');
		if($ispartnerEnabled == 1){
			$this->addColumn('group', array(  
				'header'    => Mage::helper('customer')->__('Group'),      
				'index'     => 'group',	   
			));		
		}
		$this->addColumn('order', array(
            'header'    => Mage::helper('customer')->__('Order'),
            'index'     => 'order',
			'type'      => 'text',
			"filter"    => false,
            "sortable"  => false
        ));
		$isThisEnabled = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Sellerstatus');
		if($isThisEnabled == 1){
			$this->addColumn('sellerstatus', array(
				'header'    => Mage::helper('customer')->__('Seller Status'),
				'type'      => 'text',
				'index'     => 'sellerstatus',
				"filter"    => false,
	            "sortable"  => false
			));
		}
        $this->addColumn('Telephone', array(
            'header'    => Mage::helper('customer')->__('Telephone'),
            'width'     => '100',
            'index'     => 'billing_telephone',
        ));
        $this->addColumn('billing_postcode', array(
            'header'    => Mage::helper('customer')->__('ZIP'),
            'width'     => '90',
            'index'     => 'billing_postcode',
        ));
        $this->addColumn('billing_country_id', array(
            'header'    => Mage::helper('customer')->__('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'billing_country_id',
        ));
        $this->addColumn('billing_region', array(
            'header'    => Mage::helper('customer')->__('State/Province'),
            'width'     => '100',
            'index'     => 'billing_region',
        ));
        $this->addColumn('customer_since', array(
            'header'    => Mage::helper('customer')->__('Seller Since'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true,
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }
        $this->addColumn('deny', array(
            'header'    => Mage::helper('marketplace')->__('Reason'),
            'index'     => 'deny',
            'type'  => 'text',
            'filter'    => false,
            'sortable'  => false
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('View Seller'),
                        'url'       => array('base'=> 'adminhtml/customer/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));

		$this->addExportType('*/*/exportCsv', Mage::helper('marketplace')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('marketplace')->__('XML'));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction(){
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('customer');

        $this->getMassactionBlock()->addItem('ispartner', array(
             'label'    => Mage::helper('customer')->__('Approve'),
             'url'      => $this->getUrl('*/*/massispartner')
        ));
		$this->getMassactionBlock()->addItem('isnotpartner', array(
             'label'    => Mage::helper('customer')->__('Unapprove'),
             'url'      => $this->getUrl('*/*/massnotpartner')
        ));
        $this->getMassactionBlock()->addItem('printstate', array(
            'label'    => Mage::helper('customer')->__('Vendor Statement'),
            'url'      => $this->getUrl('*/*/printstatement'),
             'additional'   => array(
                 'date_from'    => array(
                     'name'     => 'date_from',
                     'type'     => 'date',
                     'class'    => 'required-entry',
                     'label'    => Mage::helper('customer')->__('Date From'),
                     'gmtoffset' => true,
                     'image'    => Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_WEB, true ).'/skin/adminhtml/default/default/images/grid-cal.gif',
                     'format'    => '%Y-%m-%d'
                 ),
            'date_to'    => array(
                'name'     => 'date_to',
                'type'     => 'date',
                'class'    => 'required-entry',
                'label'    => Mage::helper('customer')->__('Date To'),
                'gmtoffset' => true,
                'image'    => Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_WEB, true ).'/skin/adminhtml/default/default/images/grid-cal.gif',
                'format'    => '%Y-%m-%d'
            ),
        )
        ));
        $this->getMassactionBlock()->addItem('printrefund', array(
            'label'    => Mage::helper('customer')->__('Refund Listing'),
            'url'      => $this->getUrl('*/*/printrefundlisting'),
            'additional'   => array(
                'date_from'    => array(
                    'name'     => 'date_from',
                    'type'     => 'date',
                    'class'    => 'required-entry',
                    'label'    => Mage::helper('customer')->__('Date From'),
                    'gmtoffset' => true,
                    'image'    => Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_WEB, true ).'/skin/adminhtml/default/default/images/grid-cal.gif',
                    'format'    => '%Y-%m-%d'
                ),
                'date_to'    => array(
                    'name'     => 'date_to',
                    'type'     => 'date',
                    'class'    => 'required-entry',
                    'label'    => Mage::helper('customer')->__('Date To'),
                    'gmtoffset' => true,
                    'image'    => Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_WEB, true ).'/skin/adminhtml/default/default/images/grid-cal.gif',
                    'format'    => '%Y-%m-%d'
                ),
            )
        ));



		$ispartnerEnabled = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Mppartnergroup');
		if($ispartnerEnabled == 1){	
			$groups=Mage::getModel('mppartnergroup/mppartnergroup')
									->getCollection()
									->addFieldToFilter('status',array('eq'=>1));	
			$groupsoptions=array(array('value'=>'','label'=>'Select Group'));
			foreach($groups as $group){			
				$value=$group->getGroupCode();		
				$label=$group->getGroupName();		
				array_push($groupsoptions,array('value'=>$value,'label'=>$label));	
			}    
			array_unshift($statuses, array('label'=>'', 'value'=>''));   
			$this->getMassactionBlock()
				 ->addItem('assinegroup', array(    
						'label'=> Mage::helper('catalog')->__('Assine Group'),    
						'url'  => $this->getUrl('mppartnergroup/adminhtml_mppartnergroup/massassinegroup',
									array('_current'=>true)),            
						'additional' => array(           
							'visibility' => array(     
							'name' => 'assinegroup',    
							'type' => 'select',    
							'class' => 'required-entry',        
							'label' => Mage::helper('catalog')->__('Assine Group'), 
							'values' => $groupsoptions          
							)         
						)      
					));
		}
		$isThisEnabled = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Sellerstatus');
		if($isThisEnabled == 1){
			$this->getMassactionBlock()->addItem('sellesstatus', array(
				 'label'    => Mage::helper('customer')->__('Download Status'),
				 'url'      => $this->getUrl('sellerstatus/sellerstatus/masssellerstatus')
			));
		}
        return $this;
    }
	//public function getGridUrl(){
		//return $this->getUrl("*/*/grid",array("_current"=>true));
	//}
}
