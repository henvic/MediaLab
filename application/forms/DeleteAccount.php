<?php
class Ml_Form_DeleteAccount extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('password', 'password', array(
                'filters'    => array('StringTrim'),
                'validators' => array(
                    array('validator' => 'matchPassword') //stringlenght there
                ),
                'required'   => true,
                'label'      => 'Current Password:',
            ));
        
        $this->addElement('hash', 'no_csrf_foo', 
        array('salt' => 'K*#%JQk74#$*%Ĉ#%R*b', 'timeout' => 600));
        
        $this->addElement(Ml_Model_AntiAttack::captchaElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Delete your account',
            'class'    => 'btn danger',
        ));
        
        $this->setAttrib('class', 'form-stacked');
    }
}