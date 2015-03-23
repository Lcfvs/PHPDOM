<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/reg-invoker
*/
require_once '../Document.php';
require_once '../Element.php';

class DOM_HTML_Document extends DOM_Document
{
    // doctype
    protected $_publicId = 'html';
    
    // params
    public $encoding = 'utf-8';
    public $lang = 'en';
    public $standalone = true;
    
    public function __construct($as_view = false)
    {
        parent::__construct('html', $as_view);
        
        $document = $this->_document;
        $document->registerNodeClass('DOMElement', 'DOM_HTML_Element');
        
        $html = $document->documentElement;
        
        $html->setAttribute('lang', $this->lang);
        
        $head = $html->select('head');

        if (!$head) {
            $head = $this->append([
                'tag' => 'head'
            ]);
        }

        if (!$head->select('title')) {
            $head->append([
                'tag' => 'title'
            ]);
        }
        
        $charset_meta = $head->select('meta[charset]');
        
        if ($charset_meta) {
            $charset = $charset_meta->getAttribute('charset');
            $encoding = $this->encoding;
            
            if ($charset->value !== $encoding) {
                $charset->value = $encoding;
            }
        } else {
            $head->append([
                'tag' => 'meta',
                'attributes' => [
                    'charset' => 'utf-8'
                ]
            ]);
        }
        
        if (!$html->select('body')) {
            $this->append([
                'tag' => 'body',
            ]);
        }
    }
    
    public function __get($name)
    {
        switch ($name) {
            case 'title': 
                return $this->select('title')->textContent;
            
            case 'body':
                return $this->select('body');
            
            case 'forms':
                return $this->selectAll('body form');
                
            default:
                return parent::__get($name);
        }
    }
    
    public function __set($name, $value)
    {
        switch ($name) {
            case 'title':
                $title = $this->select('title');
                $node = $title->select('*');
                
                if ($node) {
                    $node->nodeValue = $value;
                } else {
                    $title->appendChild($this->createTextNode($value));
                }
            break;
            
            case 'lang':
                $document_element = $this->_document->documentElement;
                $document_element->setAttribute('lang', $this->_lang);
            break;
            
            default:
                parent::__set($name, $value);
        }
    }
    
    public function append($definition)
    {
        return $this->_document->documentElement->append($definition);
    }
    
    public function insert($definition, $before)
    {
        return $this->_document->documentElement->insert($definition, $before);
    }
    
    public function create($definition)
    {
        return $this->_document->documentElement->create($definition);
    }
    
    public function __toString()
    {
        return substr($this->_document->saveHTML(), 0, -1);
    }
    
    public function __destruct()
    {
        $view = self::$_view;
        
        if (!$this->_isRendered && $view === $this->_document) {
            $this->_isRendered = true;
            
            echo $this;
        }
    }
}
