<?php
/**
 * Tags Controller
 *
 * (see shares controller header notes)
 *
 * @copyright  2009 Henrique Vicente
 * @version    $Id:$
 * @since      File available since Release 0.1
 */

class TagsController extends Zend_Controller_Action
{
    public function addAction()
    {
        $this->_helper->layout->disableLayout();
        
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $request = $this->getRequest();
        
        $tags = Ml_Model_Tags::getInstance();
        
        $params = $request->getParams();
        
        if ($auth->getIdentity() == $shareInfo['byUid']) {
            
            $tagsForm = $tags->form();
            
            if ($request->isPost() && $tagsForm->isValid($request->getPost())) {
                $tagsArray = $tags->makeArrayOfTags($tagsForm->getValue('tags'));
                
                //It's not guaranteed to work within the tags limit margin,
                //but it's more than ok
                $oldTags = $tags->getShareTags($shareInfo['id']);
                $tagsCounter = sizeof($oldTags);
                
                foreach ($tagsArray as $n => $tag) {
                    if ($tagsCounter >= $config['tags']['limit']) {
                        break;
                    }
                    
                    $add = $tags->addTag($shareInfo['id'], $shareInfo['byUid'], $tag['clean'], $tag['raw'], $tagsCounter);
                    
                    if ($add) {
                        $tagsCounter ++;
                    }
                }
            }
        }
        return $this->_forward("tags");
    }
    
    public function deleteAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = $this->getFrontController()->getRouter();
        
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $request = $this->getRequest();
        
        $tags = Ml_Model_Tags::getInstance();
        
        $params = $request->getParams();
        
        if ($auth->getIdentity() == $shareInfo['byUid']) {
            
            $form = $tags->deleteForm();
            
            if ($request->isPost() && $form->isValid($request->getPost())) {
                $tags->delete($params['deletetag']);
                
                if ($this->_request->isXmlHttpRequest()) {
                    $this->_forward("tags");
                } else {
                    $this->_redirect($router->assemble($params, "sharepage_1stpage"),
                    array("exit"));
                }
            }
            
            $this->view->tag = $tags->getById($params['deletetag']);
            $this->view->deleteTagForm = $form;
        }
        
    }
    
    /** this method only displays the tags for a called share or redirects
     * the user to the share's mainpage and that's all */
    public function tagsAction()
    {
        $request = $this->getRequest();
        
        $router = $this->getFrontController()->getRouter();
        
        $params = $request->getParams();
        
        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        } else {
            $this->_redirect($router->assemble($params, "sharepage_1stpage"), array("exit"));
        }
        
        $registry = Zend_Registry::getInstance();
        $shareInfo = $registry->get("shareInfo");
        
        $tags = Ml_Model_Tags::getInstance();
        
        $tagsList = $tags->getShareTags($shareInfo['id']);
        
        $this->view->tagsList = $tagsList;
    }
}