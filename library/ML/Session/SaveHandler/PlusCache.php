<?php
/**
 * ML_Session_SaveHandler_PlusCache
 *
 * @license         public domain
 * @author          Henrique Vicente <henriquevicente@gmail.com>
 * @version         $Id$
 */

class ML_Session_SaveHandler_PlusCache extends ML_Session_SaveHandler_Cache
{
	/**
     * Session prefix
     *
     * @var string
     */
    protected $_lastActivityPrefix = "la_";
    
	/**
     * Set the session prefix
     *
     * @param string $sessionPrefix
     * @return Zend_Session_SaveHandler_Cache
     */
    public function setlastActivityPrefix($lastActivityPrefix)
    {
        $this->_lastActivityPrefix = $lastActivityPrefix;

        return $this;
    }
	
	/**
     * Retrieve activity prefix
     *
     * @return string
     */
    public function getlastActivityPrefix()
    {
        return $this->_lastActivityPrefix;
    }
	
    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
    	return parent::read($id);
    }
    
    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return boolean
     */
    public function write($id, $data)
    {
    	$auth = Zend_Auth::getInstance();
    	
    	$registry = Zend_Registry::getInstance();
    	
    	$config = $registry->get("config");
    	
    	//if user is identified, save additional information
    	if($auth->hasIdentity())
    	{
	    	$request_info = array(
    			"http_user_agent" => $_SERVER['HTTP_USER_AGENT'],
    			"request_method" => $_SERVER['REQUEST_METHOD'],
    			"remote_addr" => $_SERVER['REMOTE_ADDR'],
    			"request_time" => (int)$_SERVER['REQUEST_TIME'],
    			"request_method" => $_SERVER['REQUEST_METHOD'],
    			"request_uri" => $_SERVER['REQUEST_URI'],
    			"http_response_code" => Zend_Controller_Front::getInstance()->getResponse()->getHttpResponseCode(),
    			"session" => $id,
    			"uid" => $auth->getIdentity()
    		);
    		
    		$this->_cache->save($request_info, $this->_sessionPrefix . $this->_lastActivityPrefix. $id, array(), $this->_getLifetime($id));
    		
			$client = new couchClient($config['resources']['db']['couchdb']['dsn'],"web_access_log");
			
			$request_info['_id'] = $_SERVER['REQUEST_TIME'] . "-" . mt_rand() . mt_rand();
			
			try {
				$client->storeDoc(array_to_obj($request_info));
			} catch(Exception $e) {
				error_log("Failed to store log with CouchDB for this authenticated access");
			}
    	}
    	
    	return parent::write($id, $data);
    }
    
    /**
     * Destroy session
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {    	
    	return parent::destroy($id);
    }
}