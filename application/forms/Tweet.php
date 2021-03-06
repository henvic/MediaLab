<?php

class Ml_Form_Tweet extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $registry = Zend_Registry::getInstance();
        
        $tweet = $this->addElement('textarea', 'tweet', array(
            'label'      => 'Tell them...',
            'description' => 'Tweet something…',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 140)),
                )
        ));
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
        $this->addElement('submit', 'tweetSubmit', array(
            'label'    => 'Tweet!',
            'class'    => 'btn primary'
        ));
        
        $this->setAttrib('class', 'form-stacked');
    }
}