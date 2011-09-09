<?php
class Form_Picture extends Zend_Form
{
    public function init()
    {
        $this->setAttrib('enctype', 'multipart/form-data');
        
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', 
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
        ));
        
        $this->addElement('submit', 'delete', array(
            'label'    => 'Delete current!',
        ));
        
        $this->getElement("delete")->setAttrib("class", "likelink");
        
        $this->addElement(ML_MagicCookies::formElement());
    }
}
