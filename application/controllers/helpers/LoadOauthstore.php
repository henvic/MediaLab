<?php
class Zend_Controller_Action_Helper_LoadOauthstore extends
                Zend_Controller_Action_Helper_Abstract
{
	public function preloadServer()
	{
		require_once EXTERNAL_LIBRARY_PATH . '/oauth-php/library/OAuthServer.php';
	}
	
	public function setinstance()
	{
		require_once EXTERNAL_LIBRARY_PATH . '/oauth-php/library/OAuthStore.php';
		
		$registry = Zend_Registry::getInstance();
		
		$db = $registry->get("config")->resources->db->params;
		OAuthStore::instance("MySQL", array(
			"server" => $db->host,
			"database" => $db->dbname,
			"username" => $db->username,
			"password" => $db->password
		));
	}
}