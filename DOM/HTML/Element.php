<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/reg-invoker
*/
require_once __DIR__ . '/../Element.php';

class DOM_HTML_Element extends DOM_Element
{
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

    public function create($definition)
    {
        $document = $this->ownerDocument;
        $normalized = $this->_normalize($definition);

        $node = $document->createElement($normalized->tag);
        $node->setAttributes($normalized->attributes);

        foreach ($normalized->data as $key => $line) {
            if ($key > 0) {
                $node->appendChild($document->createElement('br'));
            }

            $node->appendChild($document->createTextNode($line));
        }

        return $node;
    }

    private function _normalize($definition)
    {
        $document = $this->ownerDocument;
        $fields = $this->_fields;
        $unbreakables = $this->_unbreakables;

        $normalized = (object) $definition;

        $attributes = @$normalized->attributes;
        $before = @$normalized->before;
        $data = @$normalized->data;
        $tag = @$normalized->tag;

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

                    case 'value':
                        $value = is_null($value) ? '' : $value;

                        if ($tag === 'textarea') {
                            unset($attributes[$name]);

                            $data[] = $value;
                        } else {
                            $attributes[$name] = $value;
                        }
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

    public function __toString()
    {
        return $this->ownerDocument->saveHTML($this);
    }
}