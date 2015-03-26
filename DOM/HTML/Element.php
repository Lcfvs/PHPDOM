<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/PHPDOM
*/
require_once __DIR__ . '/NodeList.php';
require_once __DIR__ . '/Selector.php';

class DOM_HTML_Element extends DOMElement
{
    public function append($definition)
    {
        $node = $this->ownerDocument->create($definition);
        $this->appendChild($node);

        return $node;
    }

    public function decorate($definition)
    {
        $node = $this->parentNode->insert($definition, $this);
        $node->appendChild($this);

        return $node;
    }

    public function insert($definition, $before)
    {
        $node = $this->ownerDocument->create($definition);

        if ($before instanceof self) {
            $this->insertBefore($node, $before);

            return $node;
        }

        $before = $this->select($before);
        $this->insertBefore($node, $before);

        return $node;
    }

    public function prepend($definition)
    {
        $node = $this->ownerDocument->create($definition);
        $this->parentNode->insertBefore($node, $this);

        return $node;
    }

    public function select($selector)
    {
        $node_list = $this->selectAll($selector);

        if ($node_list instanceof DOM_HTML_NodeList) {
            return $node_list->item(0);
        }
    }

    public function selectAll($selector)
    {
        $query = DOM_HTML_Selector::parse($selector);
        
        $node_list = $this->ownerDocument->xpath->evaluate($query, $this);
        
        if ($node_list instanceof DOMNodeList) {
            return new DOM_HTML_NodeList($node_list);
        }
    }
    
    public function setAttributes($attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }
    
    public function __get($name)
    {
        switch ($name) {
            case 'childNodes':
                $nodes = $this->childNodes;
                
                if ($nodes) {
                    return new DOM_HTML_NodeList($nodes);
                }
                
                break;
            
            case 'parentNode':
                $node = $this->parentNode;
                
                if ($node && $node->nodeName === 'form') {
                    return new DOM_HTML_Element_Form($node);
                }
                
                break;
            case 'value':
                switch ($this->nodeName) {
                    case 'textarea':
                        return $this->nodeValue;
                    
                    case 'input':
                        switch ($this->getAttribute('type')) {
                            case 'checkbox':
                            case 'radio':
                                if ($this->getAttribute('checked') !== 'checked') {
                                    break;
                                }
                            
                            default:
                                return $this->getAttribute('value');
                        }
                        
                        break;
                        
                    case 'select':
                        $selected_option = $this->select(':scope > [selected="selected"]');
                        
                        if ($selected_option) {
                            return $selected_option->getAttribute('value');
                        }
                }
        }
    }
    
    public function __set($name, $value)
    {
        if ($name === 'value') {
            switch ($this->nodeName) {
                case 'select':
                    $selected_option = $this->select(':scope > [value="' . $value . '"]');
                    
                    if ($selected_option) {
                        $selected_option->setAttribute('selected', 'selected');
                    }
                    
                break;
                    
                case 'textarea':
                    $this->nodeValue = $value;
                    
                    break;
                
                case 'input':
                    switch ($this->getAttribute('type')) {
                        case 'checkbox':
                        case 'radio':
                            if ($value) {
                                $this->setAttribute('checked', 'checked');
                            } else {
                                $this->removeAttribute('checked');
                            }
                            
                            break;
                    
                        default:
                            $this->setAttribute('value', $value);
                    }
            }
        }
    }
    
    public function __toString()
    {
        return $this->ownerDocument->saveXML($this);
    }
}