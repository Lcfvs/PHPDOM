<?php
require_once 'DOM/HTML/Document.php';

$document = new DOM_HTML_Document();
$body = $document->select('body');

$form = $body->append([
    'tag' => 'form'
]);

$form->append([
    'tag' => 'select'
]);

$form->append([
    'tag' => 'input',
    'attributes' => [
        'type' => 'text'
    ],
    'value' => 123
]);

var_dump($document->select('form input:text'));
echo $document->select('form')->elements->item(0)->parentNode;