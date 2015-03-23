<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/reg-invoker
*/
require_once __DIR__ . '/Selector.php';

class DOM_Element extends DOMElement
{
    public function append($definition)
    {
        $node = $this->create($definition);
        $this->appendChild($node);
        
        return $node;
    }
    
    public function insert($definition, $before)
    {
        $node = $this->create($definition);
        
        if ($before instanceof self) {
            $this->insertBefore($node, $before);
            
            return $node;
        }
        
        $before = $this->select($before);
        $this->insertBefore($node, $before);
            
        return $node;
    }
    
    public function setAttributes($attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
        
        return $this;
    }
    
    public function select($selector)
    {
        $node_list = $this->selectAll($selector);
        
        if ($node_list instanceof DOMNodeList) {
            return $node_list->item(0);
        }
    }
    
    public function selectAll($selector)
    {
        $query = DOM_Selector::parse($selector);
        
        return $this->ownerDocument->xpath->evaluate($query, $this);
    }
}