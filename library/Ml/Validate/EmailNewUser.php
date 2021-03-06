<?php
//require_once 'Zend/Validate/Abstract.php';


class Ml_Validate_EmailNewUser extends Zend_Validate_Abstract
{
    const MSG_EMAIL_EXISTS = 'emailAlreadyExists';
 
    protected $_messageTemplates = array(
        self::MSG_EMAIL_EXISTS =>
         "There is already another account with this e-mail address.",
    );
 
    public function isValid($value)
    {
        $people = Ml_Model_People::getInstance();
        
        $this->_setValue($value);
 
        $valueString = (string) $value;
        
        if (mb_strlen($value) < 3 || mb_strlen($value) > 60) {
            return false;
        }
        
        $getUserByMail = $people->getByEmail($value);
        
        if (! empty($getUserByMail)) {
            $this->_error(self::MSG_EMAIL_EXISTS);
            return false;
        }
        
        return true;
    }
}
