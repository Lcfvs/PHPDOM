<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/PHPDOM
*/
namespace PHPDOM\HTML;

trait NodeTrait
{
    use SelectorTrait;
    
    public function append($definition)
    {
        return $this->insert($definition);
    }

    public function decorate($definition)
    {
        if ($this->isNode($definition)) {
            $node = $definition;
            
            if ($definition instanceof DocumentFragment) {
                $node->parent = $this;
            }
        } else {
            $node = $this->ownerDocument->create($definition);
        }
        
        if (!empty($this->parentNode)) {
            $this->parentNode->insert($node, $this);
        } else if (!empty($this->parent)) {
            $this->parent->insert($node, $this);
        }
        
        $node->append($this);

        return $node;
    }

    public function insert($definition, $before = null)
    {
        if ($this->isNode($definition)) {
            $node = $definition;
            
            if ($definition instanceof DocumentFragment) {
                $node->parent = $this;
            }
        } else {
            $node = $this->ownerDocument->create($definition);
        }

        if ($this->isNode($before)) {
            $this->insertBefore($node, $before);

            return $node;
        }

        if (gettype($before) === 'string') {
            $before = $this->select($before);
        }
        
        $this->insertBefore($node, $before);

        return $node;
    }

    public function prepend($definition)
    {
        $first_child = $this->firstChild;
        
        if ($this->isNode($definition)) {
            $node = $definition;
            
            if ($node instanceof DocumentFragment) {
                $node->parent = $this;
            }
        } else {
            $node = $this->ownerDocument->create($definition);
        }
        
        if ($first_child) {
            $this->insertBefore($node, $first_child);
        } else {
            $this->appendChild($node);
        }

        return $node;
    }

    public function children()
    {
        $nodes = $this->childNodes;

        if ($nodes) {
            return new NodeList($nodes);
        }
    }

    public function replace($definition)
    {
        if ($definition instanceof self || $definition instanceof DocumentFragment) {
            $node = $definition;
            
            if ($definition instanceof DocumentFragment) {
                $node->parent = $this;
            }
        } else {
            $node = $this->ownerDocument->create($definition);
        }
        
        if (!empty($this->parentNode)) {
            $this->parentNode->replaceChild($node, $this);
        } else if (!empty($this->parent)) {
            $this->parent->replaceChild($node, $this);
        }

        return $node;
    }
    
    public function remove()
    {
        $fragment = $this->ownerDocument->createDocumentFragment();
        
        $fragment->appendChild($this);
        
        return $this;
    }

    public function addScript($path, $directory = '/js/', array $attributes = [])
    {
        $definition = array_merge([ 
            'tag' => 'script', 
            'attributes' => [ 
                'src' => $directory . $path 
            ] 
        ], $attributes);
        
        return $this->append($definition);
    }
    
    public function isNode($value)
    {
        return $value instanceof self
        || $value instanceof DocumentFragment
        || $value instanceof Text;
    }

    public function saveSource($filename, $flags = 0, $context = null)
    {
        file_put_contents($path, $this, $flags, $context);
        
        return $this;
    }

    public function __toString()
    {
        return $this->ownerDocument->saveHTML($this);
    }
}