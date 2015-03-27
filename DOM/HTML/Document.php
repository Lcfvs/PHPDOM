<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/PHPDOM
*/
require_once __DIR__ . '/Element.php';
require_once __DIR__ . '/NodeList.php';

class DOM_HTML_Document extends DOMDocument
{
    const DEFAULT_TEMPLATE = '<!DOCTYPE html><html><head><title> </title><meta /></head><body></body></html>';
    
    // params
    public $formatOutput = false;
    public $standalone = true;
    public $preserveWhiteSpace = false;

    // rendering
    private static $_view;
    private $_asView = false;
    
    private $_xpath = null;
    private $_fields = ['input', 'select', 'textarea'];

    private $_unbreakables = [
        'a', 'abbr', 'acronym', 'area', 'audio', 'b', 'base', 'bdi', 'bdo',
        'big', 'body', 'br', 'button', 'canvas', 'cite', 'code', 'col',
        'colgroup', 'command', 'datalist', 'del', 'dfn', 'dl', 'em', 'embed',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hgroup', 'hr', 'html',
        'i', 'iframe', 'img', 'input', 'ins', 'kbd', 'keygen', 'label', 'link',
        'map', 'mark', 'meta', 'meter', 'noscript', 'object', 'ol', 'optgroup',
        'output', 'pre', 'progress', 'q', 'ruby', 's', 'samp', 'script',
        'select', 'small', 'span', 'strong', 'style', 'sub', 'sup', 'textarea',
        'time', 'title', 'tr', 'tt', 'u', 'ul', 'var', 'video', 'wbr'
    ];

    public function __construct($as_view = false, $template = null, $encoding = 'utf-8')
    {
        parent::__construct('', $encoding);

        if (empty($template)) {
            $this->loadHTML(self::DEFAULT_TEMPLATE);
            
            $this->getElementsByTagName('meta')
                ->item(0)
                    ->setAttribute('charset', $encoding);
        } else {
            @$this->loadHTMLFile($template);
        }

        $this->_xpath = new DOMXpath($this);
        $this->registerNodeClass('DOMElement', 'DOM_HTML_Element');
        $this->formatOutput = false;
        $this->preserveWhiteSpace = false;
        $this->standalone = true;
        
        if ($as_view && is_null(self::$_view)) {
            $this->_asView = true;
            self::$_view = $this;
        }
    }

    public static function getView()
    {
        $view = self::$_view;
        
        if ($view) {
            return $view;
        }
        
        return new self(true);
    }
    
    public function create($definition)
    {
        $normalized = $this->_normalize($definition);
        $tag = $normalized->tag;
        $data = $normalized->data;
        $value = $normalized->value;
        $node = $this->createElement($tag);
        $node->setAttributes($normalized->attributes);

        if (in_array($tag, $this->_fields)) { 
            if (!is_null($value)) {
                $node->value = $value;
            }
        } else {
            foreach ($normalized->data as $key => $line) {
                if ($key) {
                    $node->appendChild($this->createElement('br'));
                }

                $node->appendChild($this->createTextNode($line));
            }
        }

        return $node;
    }

    private function _normalize($definition)
    {
        $fields = $this->_fields;
        $unbreakables = $this->_unbreakables;

        $normalized = (object) $definition;

        $attributes = @$normalized->attributes;
        $before = @$normalized->before;
        $data = @$normalized->data;
        $tag = @$normalized->tag;
        @$normalized->value =
        $value = @$normalized->value;

        if (!is_array($attributes)) {
            $attributes = [];
        }

        switch (gettype($before)) {
            case 'NULL':
                $normalized->before = null;
            break;

            case 'string':
                $normalized->before = $this->querySelector($before);
            break;
        }

        if (in_array($tag, $fields)) {
            $data = [];

            foreach ($attributes as $name => $value) {
                switch ($name) {
                    case 'autocomplete':
                        $attributes[$name] = $value ? 'on' : 'off';
                    break;

                    case 'autofocus':
                    case 'disabled':
                    case 'readonly':
                    case 'required':
                        $attributes[$name] = $value ? $name : '';
                    break;
                }
            }
        } else {
            switch (gettype($data)) {
                case 'string':
                    if (in_array($tag, $unbreakables)) {
                        $data = preg_split('/\n\r?/', $data);
                    } else {
                        $data = [$data];
                    }
                break;

                default:
                    $data = [];
            }
        }

        $normalized->data = $data;
        $normalized->attributes = $attributes;

        return $normalized;
    }

    public function loadFragment($path)
    {
        $fragment = $this->createDocumentFragment();
        $fragment->appendXML(file_get_contents($path));

        return $fragment;
    }
    
    public function getElementsByTagName($tag)
    {
        $node_list = parent::getElementsByTagName($tag);
        
        if ($node_list) {
            return new DOM_HTML_NodeList($node_list);
        }
    }

    public function select($selector)
    {
        return $this->documentElement->select($selector);
    }

    public function selectAll($selector)
    {
        return $this->documentElement->selectAll($selector);
    }
    
    public function __get($name)
    {
        switch ($name) {
            case 'body':
                return $this->select('body');
            case 'forms':
                return $this->selectAll('body form');
            case 'lang':
                return $this->documentElement->getAttribute('lang');
            case 'title':
                return $this->select('title')->textContent;
            case 'xpath':
                return $this->_xpath;
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
                $document_element = $this->documentElement;
                $document_element->setAttribute('lang', $value);
            break;
            
            default:
                parent::__set($name, $value);
        }
    }

    public function __toString()
    {
        return substr($this->saveXML(), 39, -1);
    }

    public function __destruct()
    {
        if ($this->_asView) {
            echo self::$_view;
        }
    }
}