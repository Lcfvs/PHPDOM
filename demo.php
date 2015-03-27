<?php
require_once 'DOM/HTML/Document.php';

$document = new DOM_HTML_Document(true);

$document->title = 'Document title';
$document->lang = 'en';

$body = $document->select('body');

$form = $body->append([
    'tag' => 'form'
]);

$p = $form->append([
    'tag' => 'p'
]);

$label = $form->append([
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