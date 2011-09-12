<?php
class Ml_Form_Api_Authorize extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->setMethod('post');
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
        $this->addElement('submit', 'allow', array(
            'label'    => 'Yes!',
        ));
        
        $this->addElement('submit', 'deny', array(
            'label'    => 'No!',
        ));
        
    }
}
