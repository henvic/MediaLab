<?php
require_once 'Zend/Validate/Abstract.php';

class MLValidator_MatchPassword extends Zend_Validate_Abstract
{
	const MSG_WRONG_PASSWORD = 'wrongPassword';
	
    protected $_messageTemplates = array(
        self::MSG_WRONG_PASSWORD => "Wrong password",
    );
 
    public function isValid($value)
    {
    	$Credential = ML_Credential::getInstance();
    	
        $this->_setValue($value);
 		
        $valueString = (string) $value;
        
        if(mb_strlen($value) < 6 || mb_strlen($value) > 20) return false;
        
    	$credentialInfoData = Zend_Registry::getInstance()->get('credentialInfoDataForPasswordChange');
        
        $adapter = $Credential->getAuthAdapter($credentialInfoData['uid'], $value);
        $authenticate = $adapter->authenticate();
        
        if($authenticate->getCode() != Zend_Auth_Result::SUCCESS)
        {
			$this->_error(self::MSG_WRONG_PASSWORD);
			return false;
		}
		
        return true;
    }
}