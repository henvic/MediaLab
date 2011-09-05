<?php
/**
 * Class that helps the system to protect against malicious
 * attacks from non-identified users.
 * 
 * It helps identifying suspect activity and can be used
 * for limiting access to the resources of the website
 * 
 * The access codes works with a white list approach. 
 * If something strange is happening like trying to
 * login multiply times without success, the default
 * (access free) is hardened (ensure human).
 * 
 * If strange behavor still happens again and again,
 * the access is forbidden.
 * 
 * @author henrique vicente
 *
 */

class ML_AntiAttack extends ML_getModel
{
    /**
     * Access forbidden.
     */
    const ACCESS_FORBIDDEN               =  0;

    /**
     * Access not authorized, should ensure first if
     * the response is not generated by a computer (e.g., using captcha).
     */
    const ACCESS_ENSURE_HUMAN            = -1;

    /**
     * Access free.
     */
    const ACCESS_FREE                    =  1;
    
    const WRONG_CREDENTIAL                  = 'wrongCredential';
    
    protected $_name = "antiattack";
    
    public static function loadRules()
    {
        $AntiAttack = new self();
        if($AntiAttack->getCode() == ML_AntiAttack::ACCESS_FORBIDDEN)
        {
            throw new Exception('You are forbidden to access this resource.', 403);
        }
    }
    
    public static function captchaElement()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $keys = $config['services']['recaptcha']['keys'];
        
        $recaptcha = new Zend_Service_ReCaptcha($keys['public'], $keys['private'], 
        array("ssl" => true, "xhtml" => true));
        
        $recaptcha->setOption('theme', 'clean');
        
        $captcha = new Zend_Form_Element_Captcha('challenge',
              array('label' => 'Type the challenge below',
                      'captcha'        => 'ReCaptcha',
                    'captchaOptions' => array('captcha' => 'ReCaptcha', 'service' => $recaptcha)));
        
        return $captcha;
    }
    
    
    public static function log($reason)
    {
        $AntiAttack = new self();
        if(is_string($reason)) $reason = array($reason);
        $AntiAttack->insert(array("ip" => $_SERVER['REMOTE_ADDR'], "annotations" => serialize($reason)));
        return $AntiAttack->getAdapter()->lastInsertId();
    }
    
    public static function ensureHuman()
    {
        return (self::getCode() == self::ACCESS_ENSURE_HUMAN) ? true : false;
    }
    
    public static function getCode()
    {
        $AntiAttack = new self();
        $registry = Zend_Registry::getInstance();
        
        if($registry->isRegistered('cacheAntiAttackBehavior')) return $registry->get('cacheAntiAttackBehavior');
        
        $select = $AntiAttack->select()->where("ip = ?", $_SERVER['REMOTE_ADDR'])->where("CURRENT_TIMESTAMP < TIMESTAMP(timestamp, '00:15:00')");
        $ip = substr(long2ip(ip2long($_SERVER['REMOTE_ADDR'])), 0, -2).'%';
        $select->orWhere("ip LIKE ?", $ip)->where("CURRENT_TIMESTAMP < TIMESTAMP(timestamp, '00:05:00')");
        $loggedMetaInfo = $AntiAttack->fetchAll($select);
        
        //avoid DoS attacks... And if something happens with the connection with the database, it may be handy also.
        if(!is_object($loggedMetaInfo)) $behavior = ML_AntiAttack::ACCESS_FORBIDDEN;
        else {
            $loggedMetaInfoData = $loggedMetaInfo->toArray();
            $size = sizeof($loggedMetaInfoData);
            if($size > 250) $behavior = ML_AntiAttack::ACCESS_FORBIDDEN;
            elseif($size > 8) $behavior = ML_AntiAttack::ACCESS_ENSURE_HUMAN;
            else $behavior = ML_AntiAttack::ACCESS_FREE; //It defaults to ACCESS_FREE 
        }
        
        $registry->set("cacheAntiAttackBehavior", $behavior);
        
        return $behavior;
    }
    
    ////
    // Returns a random code of the specified length, containing characters that are
    // equally likely to be any of the digits, uppercase letters, or  lowercase letters.
    //
    // The default length of 10 provides 839299365868340224 (62^10) possible codes.
    //
    // NOTE: Do not call wt_srand().  It is handled automatically in PHP 4.2.0 and above
    //       and any additional calls are likely to DECREASE the randomness.
    // @from http://www.codeaholics.com/randomCode.php
    ////
    public static function randomCode($length=10){
        $retVal = "";
        while(strlen($retVal) < $length){
            $nextChar = mt_rand(0, 61); // 10 digits + 26 uppercase + 26 lowercase = 62 chars
            if(($nextChar >=10) && ($nextChar < 36)){ // uppercase letters
                $nextChar -= 10; // bases the number at 0 instead of 10
                $nextChar = chr($nextChar + 65); // ord('A') == 65
            } else if($nextChar >= 36){ // lowercase letters
                $nextChar -= 36; // bases the number at 0 instead of 36
                $nextChar = chr($nextChar + 97); // ord('a') == 97
            } else { // 0-9
                $nextChar = chr($nextChar + 48); // ord('0') == 48
            }
            $retVal .= $nextChar;
        }
        return $retVal;
    }
    
}
