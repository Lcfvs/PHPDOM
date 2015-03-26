<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/PHPDOM
*/
class DOM_HTML_Element_Form extends DOM_HTML_Element
{
    private $_form = null;
    
    public function __construct(DOM_HTML_Element $form)
    {
        $this->_form = $form;
    }
    
    public function __get($name)
    {
        if ($name === 'elements') {
            return $this->_form->selectAll('input, select, textarea');
        }
        
        return $this->_form->{$name};
    }
    
    public function __set($name, $value)
    {
        $this->_form->{$name} = $value;
    }
    
    public function __call($method, $arguments)
    {
        return call_user_func_array([
            $this->_form,
            $method
        ], $arguments);
    }
    
    public function __toString()
    {
        return $this->_form->__toString();
    }
}