<?php
namespace PHPDOM;

require_once 'autoloader.php';

$document = new HTML\Document(true);
$document->title = 'Document title';
$document->lang = 'en';
$body = $document->body;

$form = $body->append([
    'tag' => 'form'
]);

$p = $body->select('form')->append([
    'tag' => 'p'
]);

$label = $p->append([
    'tag' => 'label',
    'data' => 'label :'
]);

$label->append([
    'tag' => 'input',
    'attributes' => [
        'type' => 'text'
    ],
    'value' => 'an escaped value "\''
]);

$form->appendChild($document->loadFragment('./fragment.html'));