<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/PHPDOM
*/
class DOM_HTML_NodeList
{
    private $_nodeList = null;
    
    public function __construct(DOMNodeList $node_list)
    {
        $this->_nodeList = $node_list;
    }
    
    function __get($name)
    {
        if ($name === 'length') {
            return $this->_nodeList->length;
        }
        
        throw new Exception('Undefined property ' . $name);
    }
    
    public function item($index)
    {
        $node = $this->_nodeList->item($index);
        
        return $node;
    }
    
    public function __toString()
    {
        $data = '';
        $index = 0;
        $length = $this->length;
        
        for (; $index < $length; $index += 1) {
            $data .= $this->item($index);
        }
        
        return $data;
    }
}