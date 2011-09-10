<?php

class ApiController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->loadOauthstore->setinstance();
    }
    
    public function authorizeAction()
    {
        $auth = Zend_Auth::getInstance();
        
        $store = OAuthStore::instance();
        
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        
        if (! $auth->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new Ml_Plugins_LoginRedirect());
        }
        
        $this->_helper->loadOauthstore->preloadServer();
        $server = new OAuthServer();
        
        $form = $this->_authorizeForm();
        // Check if there is a valid request token in the current request
        // Returns an array with the
        //consumer key, consumer secret, token, token secret and token type.
        $rs = $server->authorizeVerify();
        $consumer = $store->getConsumer($rs['consumer_key'], $auth->getIdentity());
        $this->view->consumerInfo = $consumer;
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $values = $form->getValues();
            if (isset($values['allow'])) {
                $authorized = true;
            } else if (isset($values['deny'])) {
                $authorized = false;
            }
            
            if (isset($authorized)) {
                $server->authorizeFinish($authorized, $auth->getIdentity());
                //If no oauth_callback, the user is redirected to
                $this->_redirect($router->assemble(array(), "accountapps") . "?new_addition", array("exit"));
            }
        }
        
        $this->view->authorizeForm = $form;
    }
    
    public function keysAction()
    {
        $store = OAuthStore::instance();
        $auth = Zend_Auth::getInstance();
        
        if (! Zend_Auth::getInstance()->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new Ml_Plugins_LoginRedirect());
        }
        
        $listConsumers = $store->listConsumers($auth->getIdentity());
        
        $this->view->listConsumers = $listConsumers;
    }
    
    public function applyAction()
    {
        $auth = Zend_Auth::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $store = OAuthStore::instance();
        
        if (! $auth->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new Ml_Plugins_LoginRedirect());
        }
        
        $request = $this->getRequest();
        
        $form = $this->_apiKeyForm();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $data = $form->getValues();
            
            $consumer = array(
                "requester_name" => "default",
                "requester_email" => "default",
                "callback_uri" => $data['callback_uri'],
                "application_uri" => $data['application_uri'],
                "application_title" => $data['application_title'],
                "application_descr" => $data['application_descr'],
                "application_notes" => $data['application_notes'],
                "application_commercial" => $data['commercial']
            );
            
            $key = $store->updateConsumer($consumer, $auth->getIdentity());
            
            $this->_redirect($router->assemble(array("api_key" => $key), "api_key"), array("exit"));
        }
        
        $this->view->apiKeyForm = $form;
    }
    
    public function keyeditAction()
    {
        $auth = Zend_Auth::getInstance();
        $store = OAuthStore::instance();
        
        if (! Zend_Auth::getInstance()->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new Ml_Plugins_LoginRedirect());
        }
        
        $request = $this->getRequest();
        
        $params = $request->getParams();
        
        $consumer = $store->getConsumer($params['api_key'], $auth->getIdentity());
        
        $form = $this->_apiKeyForm($consumer);
        
        $form->setDefaults($consumer);
        
        if ($consumer['application_commercial'] == 1) {
            $form->getElement("application_commercial")->setOptions(array("checked" => true));
        }
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $data = $form->getValues();
            
            $consumer = array_merge($consumer, array(
                "callback_uri" => $data['callback_uri'],
                "application_uri" => $data['application_uri'],
                "application_title" => $data['application_title'],
                "application_descr" => $data['application_descr'],
                "application_notes" => $data['application_notes'],
                "application_commercial" => $data['application_commercial'],
            ));
            
            $store->updateConsumer($consumer, $auth->getIdentity());
        }
        
        $this->view->consumerData = $consumer;
        $this->view->apiKeyForm = $form;
    }
    
    public function keydeleteAction()
    {
        $auth = Zend_Auth::getInstance();
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $store = OAuthStore::instance();
        
        if (! $auth->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new Ml_Plugins_LoginRedirect());
        }
        
        $request = $this->getRequest();
        
        $params = $request->getParams();
        
        $consumer = $store->getConsumer($params['api_key'], $auth->getIdentity());
        
        $form = $this->_deleteForm($consumer);
        if ($request->isPost() &&
         $form->isValid($request->getPost())) {
            $store->deleteConsumer($params['api_key'], $auth->getIdentity());
            $this->_redirect($router->assemble(array(), "apikeys") . "?api_key_deleted=".$params['api_key'], array("exit"));
        }
        
        $this->view->form = $form;
        $this->view->consumerData = $consumer;
    }
    
    protected function _apiKeyForm($consumer = false)
    {
        static $form = '';
        
        $registry = Zend_Registry::getInstance();
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            require APPLICATION_PATH . '/forms/api/apiKey.php';
            
            if (! $consumer) {
                $action = $router->assemble(array(), "apply_api_key");
            } else {
                $action = $router->assemble(array("api_key" => $consumer['consumer_key']), "api_key");
            }
            
            $form = new Form_APIkey(array(
                'action' => $action,
                'method' => 'post',
            ));
        }
        
        return $form;
    }
    
    protected function _deleteForm($consumer)
    {
        static $form = '';
        
        if (! is_object($form)) {
            
            $registry = Zend_Registry::getInstance();
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            require APPLICATION_PATH . '/forms/api/apiKeyDelete.php';
            
            $form = new Form_DeleteApiKey(array(
                'action' => $router->assemble(array("api_key" => $consumer['consumer_key']), "api_key_delete"),
                'method' => 'post',
            ));
        }
        
        return $form;
    }
    
    protected function _authorizeForm()
    {
        static $form = '';
        
        $registry = Zend_Registry::getInstance();
        
        if (! is_object($form)) {
            require APPLICATION_PATH . '/forms/api/authorize.php';
            
            $form = new Form_authorize(array(
                'action' => '',
                'method' => 'post',
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));
        
        return $form;
    }
}