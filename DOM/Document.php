<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/reg-invoker
*/
require_once __DIR__ . '/Element.php';

abstract class DOM_Document
{
    protected $_document;
    protected static $_view;

    // doctype
    protected $_publicId = '';
    protected $_qualifiedName = '';
    protected $_systemId = '';

    // params
    public $encoding = 'utf-8';
    public $formatOutput = false;
    public $version = '1.0';
    public $standalone = true;
    public $preserveWhiteSpace = false;

    // template
    protected $_template = null;

    // rendering
    protected $_isRendered = false;

    public function __construct($root = 'xml', $as_view = false)
    {
        $implementation = new DOMImplementation();

        $doctype = $implementation->createDocumentType(
            $this->_publicId, $this->_systemId, $this->_qualifiedName
        );

        $this->_document =
        $document = $implementation->createDocument(
            $this->version, $root, $doctype
        );

        if (!empty($template)) {
            @$document->load($template);
        }

        $document->xpath = new DOMXpath($document);
        $document->registerNodeClass('DOMElement', 'DOM_Element');
        $document->encoding = $this->encoding;
        $document->formatOutput = $this->formatOutput;
        $document->preserveWhiteSpace = $this->preserveWhiteSpace;
        $document->standalone = $this->standalone;

        if ($as_view) {
            self::$_view = $document;
        }
    }

    public function loadFragment($path)
    {
        $fragment = $this->_document->createDocumentFragment();
        $fragment->appendXML(file_get_contents($path));

        return $fragment;
    }

    public static function getView()
    {
        return self::$_view;
    }

    public function __get($name)
    {
        if ($name === 'xpath') {
            return $this->_document->xpath;
        }
    }

    public function select($selector)
    {
        $document = $this->_document;

        return $document->documentElement->select($selector);
    }

    public function selectAll($selector)
    {
        $document = $this->_document;

        return $document->documentElement->selectAll($selector);
    }

    public function __call($method, $arguments)
    {
        try {
            return call_user_func_array([
                $this->_document,
                $method
            ], $arguments);
        } catch (Exception $e) {
            $message = 'Unknown method : '
            . $method . ' in ' . get_called_class();

            throw new Exception($message);
        }
    }
}