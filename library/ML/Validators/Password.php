<?php
require_once 'Zend/Validate/Abstract.php';


class MLValidator_Password extends Zend_Validate_Abstract
{
	const MSG_WRONG_PASSWORD = 'wrongPassword';
	
    protected $_messageTemplates = array(
        self::MSG_WRONG_PASSWORD => "Wrong password",
    );
 
    public function isValid($value, $context = null)
    {	
    	$registry = Zend_Registry::getInstance();
    	
    	$Credential = ML_Credential::getInstance();
    	
        $this->_setValue($value);
 		
        $valueString = (string) $value;
        
        if(mb_strlen($value) < 6 || mb_strlen($value) > 20) return false;
        
        if(!$registry->isRegistered('loginUserInfo')) return false;
        $loginUserInfo = $registry->get('loginUserInfo');
        
		$adapter = $Credential->getAuthAdapter($loginUserInfo['id'], $value);
		
	    // Get our authentication adapter and check credentials
        if($adapter) {
        	$auth    = Zend_Auth::getInstance();
            $result  = $auth->authenticate($adapter);
            
            if($result->isValid()) {
            	return true;
        	}
        	
            $this->_error(self::MSG_WRONG_PASSWORD);
            ML_AntiAttack::log(ML_AntiAttack::WRONG_CREDENTIAL);
        }
        
        return false;
    }
}