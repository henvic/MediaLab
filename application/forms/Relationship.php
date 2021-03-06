<?php

class Ml_Form_Relationship extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $contactRelation = $this->addElement('checkbox', 'contact_relation', 
        array('label' => 'Contact'));
         /*
        $this->addElement('checkbox', 'friend_relation', array(
            'label'    => 'Friend'));*/
        
        $this->addElement('submit', 'update_relation', array(
            'label'    => "Change relation",
            'required' => false
        ));
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
        $this->setAttrib('class', 'form-stacked');
    }
}
