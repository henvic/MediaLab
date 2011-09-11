<?php
class Form_authorize extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->setMethod('post');
        
        $this->addElement(Ml_MagicCookies::formElement());
        
        $this->addElement('submit', 'allow', array(
            'label'    => 'Yes!',
        ));
        
        $this->addElement('submit', 'deny', array(
            'label'    => 'No!',
        ));
        
    }
}
