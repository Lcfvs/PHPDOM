<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/PHPDOM
*/
namespace PHPDOM\HTML;

class Dataset
{
	const CAMEL_CASE_PATTERN = '/^([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)$/';
	
	private $_element;
	
	public function __construct(Element $element)
	{
		$this->_element = $element;
	}
	
	public function __get($attr_name)
	{
		$normalized = $this->_normalize($attr_name);
		return $this->_element->getAttribute($normalized);
	}
	
	public function __set($attr_name, $value)
	{
		$normalized = $this->_normalize($attr_name);
		
		$this->_element->setAttribute($normalized, $value);
	}
	
	public function getAll()
	{
		$attributes = $this->_element->getAttributes();
		
		foreach ($attributes as $key => $value) {
			if (strpos($key, 'data-') !== 0) {
				unset($attributes[$key]);
			}
		}
		
		return $attributes;
	}
	
	private function _normalize($name){
		if (!preg_match_all(self::CAMEL_CASE_PATTERN, $name, $matches)) {
			throw new \Exception('Invalid attribute name : ' . $name);
		}
		
		$names = $matches[0];
		
	    foreach ($names as &$match) {
		    $match = ($match == strtoupper($match)
				? strtolower($match)
				: lcfirst($match));
	    }
		
        return 'data-' . implode('_', $names);
	}
}