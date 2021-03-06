<?php
class Ml_Model_Session extends Ml_Model_AccessSingleton
{
    const OPEN_STATUS = "open";
    const CLOSE_STATUS = "close";
    const CLOSE_REMOTE_STATUS = "close_remote";
    const CLOSE_GC_STATUS = "close_gc";
    const RECENT_ACCESS_INTERVAL = "2 DAY";
    const RECENT_ACCESS_SECONDS = "172800";
    
    protected $_cache = '';
    
    protected $_sessionPrefix;
    
    protected $_lastActivityPrefix;
    
    protected static $_dbTableName = "user_sessions_lookup";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    public final function __construct($config = array())
    {
        $registry = Zend_Registry::getInstance();
        
        $handler = $registry->get("memCache");
        
        $this->_cache = $handler;
        
        $sessionHandler = Zend_Session::getSaveHandler();
        
        $this->_sessionPrefix = $sessionHandler->getSessionPrefix();
        
        $this->_lastActivityPrefix = $sessionHandler->getLastActivityPrefix();
        
        parent::__construct($config);
    }
    
    /**
     * Retrieve session prefix
     *
     * @return string
     */
    protected function getSessionPrefix()
    {
        return $this->_sessionPrefix;
    }
    
    /**
     * Retrieve activity prefix
     *
     * @return string
     */
    public function getLastActivityPrefix()
    {
        return $this->_lastActivityPrefix;
    }
    
    /**
     * Meta session garbage collector
     * 
     * @param big int $uid
     * 
     * @return array of closed old sessions
     */
    public function meta_gc($uid)
    {
        $list = $this->listRecentSessionsMeta($uid, true);
        
        $close = array();
        
        foreach ($list as $key => $oneSession) {
            if ($oneSession['status'] != self::OPEN_STATUS) {
                continue;
            }
            
            $testSession = $this->_cache->test($this->getSessionPrefix() . $oneSession['session']);
            
            if (! $testSession) {
                $stmt = 'UPDATE ' . $this->_dbAdapter->quoteTableAs($this->_dbTable->getTableName()) .
                ' SET `status` = ?, `end` = CURRENT_TIMESTAMP WHERE `uid` = ? AND `session` = ?';
                
                $this->_dbAdapter->query($stmt,
                 array(self::CLOSE_GC_STATUS, $uid, $oneSession['session']));
                
                $close[] = $oneSession['session'];
            }
        }
        
        return $close;
    }
    
    public function removeSessions($list, $exception = null)
    {
        foreach ($list as $oneSession) {
            if ($oneSession['session'] != $exception) {
                $this->_cache->remove($this->_sessionPrefix . $oneSession['session']);
            }
        }
    }
    
    public function removeAllSessions($uid)
    {
        $sessionsList = $this->listRecentSessionsMeta($uid, true);
        
        $this->removeSessions($sessionsList);
    }
    
    public function logout()
    {
        $logger = Ml_Model_Logger::getInstance();
        
        $auth = Zend_Auth::getInstance();
        
        $logger->log(array("action" => "logout_request"));
        
        $oldUid = $auth->getIdentity();
        
        $auth->clearIdentity();
        
        $oldSid = Zend_Session::getId();
        
        Zend_Session::regenerateId();
        Zend_Session::destroy(true);
        
        Zend_Session::expireSessionCookie();
        $stmt = 'UPDATE ' . $this->_dbAdapter->quoteTableAs($this->_dbTable->getTableName()) .
         ' SET `status` = ?, `end` = CURRENT_TIMESTAMP, `end_remote_addr` = ? WHERE `session` = ?';
        
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $remoteAddr = $_SERVER['REMOTE_ADDR'];
        } else {
            $remoteAddr = null;
        }
        
        $this->_dbAdapter->query($stmt,
         array(self::CLOSE_STATUS, $remoteAddr, $oldSid));
    }
    
    public function remoteLogout()
    {
        $logger = Ml_Model_Logger::getInstance();
        
        $auth = Zend_Auth::getInstance();
        
        $logger->log(array("action" => "remote_logout_request"));
        
        $sessionsList = $this->listRecentSessionsMeta($auth->getIdentity());
        
        $currentSid = Zend_Session::getId();
        
        $this->removeSessions($sessionsList, $currentSid);
        
        $stmt = 'UPDATE '.
            $this->_dbAdapter->quoteTableAs($this->_dbTable->getTableName()).' '.
            'SET `status` = ?, `end` = CURRENT_TIMESTAMP, `end_remote_addr` = ? '.
            'WHERE `status` = ? AND `uid` = ? AND `session` != ?';
        
        $this->_dbAdapter->query($stmt, array(
                self::CLOSE_REMOTE_STATUS,
                ($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
                self::OPEN_STATUS,
                $auth->getIdentity(),
                $currentSid));
    }
    
    protected function listRecentSessionsMeta($uid, $onlyOpen = false)
    {
        $sql = "SELECT * FROM " . $this->_dbAdapter
        ->quoteTableAs($this->_dbTable->getTableName()) . " WHERE uid = ? AND (status = ?";

        if (! $onlyOpen) {
            $sql .= " OR end > DATE_SUB(NOW(), INTERVAL " . self::RECENT_ACCESS_INTERVAL . ")";
        }
        
        $sql .= ") ORDER BY creation desc, end desc";
        
        $bind = array($uid, self::OPEN_STATUS);
        
        $query = $this->_dbAdapter->query($sql, $bind);
        
        $result = $query->fetchAll();
        
        if (is_array($result)) {
            $dataResult = $result;
        } else {
            $dataResult = array();
        }
        
        return $dataResult;
    }
    
    /**
     * 
     * Gets a array with the information about the sessions the user is logged in at the time
     * ordened from the last to the first
     * @param big int $uid
     */
    public function getRecentActivity($uid)
    {
        $this->meta_gc($uid);
        
        $sessionsList = $this->listRecentSessionsMeta($uid);
        
        $activity = array();
        
        foreach ($sessionsList as $oneSession) {
            $lastActivityInfo = $this->_cache->load($this->_lastActivityPrefix . $oneSession['session']);
            
            if (is_array($lastActivityInfo)) {
                $lastActivityInfo['session'] = $oneSession['session'];
                
                $lastActivityInfo['status'] = $oneSession['status'];
                
                if (extension_loaded("geoip") &&
                 geoip_db_avail(GEOIP_COUNTRY_EDITION) &&
                 isset($lastActivityInfo['remote_addr'])) {
                    $lastActivityInfo['geo'] = @geoip_record_by_name($lastActivityInfo['remote_addr']);
                } else {
                    $lastActivityInfo['geo'] = false;
                }
                
                if (isset($lastActivityInfo['request_time']) &&
                 $lastActivityInfo['request_time'] >
                 ((int) $_SERVER['REQUEST_TIME'] - self::RECENT_ACCESS_SECONDS)) {
                    $activity[] = $lastActivityInfo;
                }
            }
        }
        
        return $activity;
    }
    
    public function associate($uid, $sessionId)
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $remoteAddr = $_SERVER['REMOTE_ADDR'];
        } else {
            $remoteAddr = null;
        }
        
        $data = array(
            "uid" => $uid,
            "session" => $sessionId,
            "status" => self::OPEN_STATUS,
            "creation_remote_addr" => $remoteAddr);
        
        $this->_dbTable->insert($data);
    }
}

