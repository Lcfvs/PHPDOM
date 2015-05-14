<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/PHPDOM
*/
namespace PHPDOM\HTML;

class Document extends \DOMDocument
{
    const DEFAULT_TEMPLATE = '<!DOCTYPE html><html><head><title></title></head><body></body></html>';

    public $formatOutput = false;
    public $standalone = true;
    public $preserveWhiteSpace = false;
    
    private $_bodyScripts = [];

    private $_xpath = null;

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

    public function __construct($load_default_template = false, $encoding = 'utf-8')
    {
        parent::__construct('1.0', $encoding);

        $this->encoding = $encoding;
        
        if ($load_default_template) {
            $this->loadSource(static::DEFAULT_TEMPLATE);
        }
    }
    
    public function loadSource($source, $options = LIBXML_NOWARNING)
    {
        $this->loadHTML($source, $options);

        $encoding = $this->encoding;
        
        $this->_xpath = new \DOMXpath($this);
        $this->registerNodeClass('\\DOMNode', 'PHPDOM\\HTML\\Node');
        $this->registerNodeClass('\\DOMElement', 'PHPDOM\\HTML\\Element');
        $this->registerNodeClass('\\DOMText', 'PHPDOM\\HTML\\Text');
        $this->registerNodeClass('\\DOMDocumentFragment', 'PHPDOM\\HTML\\DocumentFragment');
        
        $this->formatOutput = false;
        $this->preserveWhiteSpace = false;
        $this->standalone = true;

        $meta = $this->select('head meta[charset]');
        
        if ($meta) {
            $meta->setAttribute('charset', $encoding);
        } else {
            $this->select('head')
                ->append([
                    'tag' => 'meta',
                    'attributes' => [
                        'charset' => $encoding
                    ]
                ]);
        }
        
        return $this;
    }

    public function loadSourceFile(
        $filename,
        $options = LIBXML_NOWARNING,
        $use_include_path = false,
        $context,
        $offset = -1,
        $maxlen
    )
    {
        $source = file_get_contents(
            $filename,
            $use_include_path,
            $context,
            $offset,
            $maxlen
        );
        
        return $this->loadSource($source, $options);
    }

    public function create($definition)
    {
        $type = gettype($definition);
        
        if ($type !== 'array' && $type !== 'object') {
            $definition = strval($definition);
            $fragment = $this->createDocumentFragment();
            $lines = preg_split('/\n\r?/', $definition);
            
            foreach ($lines as $key => $line) {
                if ($key) {
                    $fragment->append([
                        'tag' => 'br'
                    ]);
                }
                
                $text_node = $this->createTextNode($line);
                $fragment->append($text_node);
            }
            
            return $fragment;
        }
    
        $normalized = $this->_normalize($definition);
        $tag = $normalized->tag;
        $data = $normalized->data;
        $node = $this->createElement($tag);
        $node->setAttributes($normalized->attributes);

        if (!empty($data)) {
            $node->append($data);
        }
        
        foreach ($normalized->children as $child) {
            $node->append($child);
        }

        return $node;
    }

    private function _normalize($definition)
    {
        $normalized = (object) $definition;

        $attributes = @$normalized->attributes;
        $before = @$normalized->before;
        $data = @$normalized->data;
        $tag = @$normalized->tag;
        $children = @$normalized->children;
        @$normalized->value =
        $value = @$normalized->value;

        if (!is_array($children)) {
            $normalized->children = [];
        }

        if (!is_array($attributes)) {
            $normalized->attributes = [];
        }

        switch (gettype($before)) {
            case 'NULL':
                $normalized->before = null;
            break;

            case 'string':
                $normalized->before = $this->querySelector($before);
            break;
        }

        $normalized->data = $data;

        return $normalized;
    }

    public function loadFragmentFile(
        $filename,
        $options = LIBXML_NOWARNING,
        $use_include_path = false,
        $context,
        $offset = -1,
        $maxlen
    )
    {
        $source = file_get_contents(
            $filename,
            $use_include_path,
            $context,
            $offset,
            $maxlen
        );

        return $this->loadFragment($source, $options);
    }

    public function loadFragment($source, $options = LIBXML_NOWARNING)
    {
        $fragment = $this->createDocumentFragment();
        $fragment->appendXML($source, $options);

        return $fragment;
    }

    public function getElementsByTagName($tag)
    {
        $node_list = parent::getElementsByTagName($tag);

        if ($node_list) {
            return new NodeList($node_list);
        }
    }
    
    public function addStyleSheet($path, $directory = '/css/', array $attributes = [])
    { 
        return $this->select('head')->append(array_merge([ 
            'tag' => 'link', 
            'attributes' => [ 
                'rel' => 'stylesheet', 
                'href' => $directory . $path
            ] 
        ], $attributes)); 
    }
    
    public function addHeadScript($path, $directory = '/css/', array $attributes = [])
    { 
        return $this->select('head')->addScript($path, $directory, $attributes); 
    }
    
    public function addBodyScript($path, $directory = '/css/', array $attributes = [])
    {
        $definition = array_merge([ 
            'tag' => 'script', 
            'attributes' => [ 
                'src' => $directory . $path 
            ] 
        ], $attributes);
        
        $script = $this->create($definition);
        $this->_bodyScripts[] = $script;
        
        return $script; 
    }
    
    public function select($selector)
    {
        return $this->documentElement->select($selector);
    }

    public function selectAll($selector)
    {
        return $this->documentElement->selectAll($selector);
    }

    public function saveSource($filename, $flags = 0, $context = null)
    {
        file_put_contents($path, $this, $flags, $context);
        
        return $this;
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
                $node = $title->childNodes->item(0);

                if ($node) {
                    $node->nodeValue = $value;
                } else {
                    $title->appendChild($this->create($value));
                }
            break;

            case 'lang':
                $document_element = $this->documentElement;
                $document_element->setAttribute('lang', $value);
            break;
        }
    }

    public function __toString()
    {
        foreach ($this->_bodyScripts as $script) {
            $this->body->appendChild($script);
        }
        
        $this->_bodyScripts = [];
        
        return substr($this->saveHTML(), 0, -1);
    }
}