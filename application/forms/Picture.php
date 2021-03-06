<?php

class Ml_Form_Picture extends Zend_Form
{
    public function init()
    {
        $this->setAttrib('enctype', 'multipart/form-data');
        
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $file = new Zend_Form_Element_File('Image');
        $file->setLabel('Choose a picture:');
        $file->addValidator('Count', false, 1);
        $file->addValidator('Size', false, array('max' => '1MB'));
        $file->setMaxFileSize(2048*1024);
        $file->addValidator('Extension', false, 'jpg,png,gif');
        $file->setRequired(false);
        $file->setOptions(Array('ignoreNoFile' => true));
        $this->addElement($file, 'Image');
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Submit!',
            'class'    => 'btn primary',
        ));
        
        $this->addElement('submit', 'delete', array(
            'label'    => 'Delete current',
            'class'    => 'btn danger',
        ));
        
        $this->setAttrib('class', 'form-stacked');
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
    }
}
