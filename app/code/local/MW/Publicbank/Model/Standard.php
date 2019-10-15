<?php 
class MW_Publicbank_Model_Standard extends Mage_Payment_Model_Method_Cc
{

    protected $_code  = 'publicbank_standard';

    protected $_isGateway               = true;
    protected $_canAuthorize            = false;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = false;
    protected $mw_redirectUrl				= null;
	protected $mw_PaReq= null;
	protected $mw_TM_TermUrl= null;
	protected $mw_MD= null;
	protected $just_test = 'Test';
     
    const STATUS_APPROVED = 'APPROVED';
	const PAYMENT_ACTION_AUTH_CAPTURE = 'authorize_capture';
	const PAYMENT_ACTION_AUTH = 'authorize';
		
	public function getUsername()
	{		
		return Mage::getStoreConfig('payment/cgg_standard/username');		
	}
	public function setTest($just_test)
	{		
		$this->just_test = 	$just_test;
	}
	public function getTest()
	{		
		return $this->just_test;
	}

    public function assignData($data)
    {
        parent::assignData($data);
        Mage::getSingleton('core/session')->setCredit($data);
        return $this;
    }
	
	public function getPassword()
	{		
		return Mage::getStoreConfig('payment/cgg_standard/password');		
	}
	public function getTerminalNumber()
	{		
		return Mage::getStoreConfig('payment/cgg_standard/terminal_number');		
	}
	function getOrderPlaceRedirectUrl() {
            return Mage::getUrl('publicbank/submitform/index', array('_secure' => true));
	}

}
?>
