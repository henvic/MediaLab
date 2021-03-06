<?php
class Ml_Form_Api_Setmeta extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $title = $this->addElement('text', 'title', array(
            'label'      => 'Title:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 100))
                )
        ));
        
        $filename = $this->addElement('text', 'filename', array(
            'label'      => 'Filename:',
            'required'   => true,
            'filters'    =>
                array('StringTrim', 'StringToLower', 'FilenameRobot'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 60)),
                array('validator' => 'filename')
                )
        ));
        
        
        $short = $this->addElement('text', 'short', array(
            'label'      => 'Short description:',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 120)),
                )
        ));
        
        $description = $this->addElement('textarea', 'description', array(
            'label'      => 'Description:',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 4096)),
                )
        ));
    }
}