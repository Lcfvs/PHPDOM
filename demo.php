<?php
require_once 'DOM/HTML/Document.php';

$view = new DOM_HTML_Document(true);
$view->title = 'DOM_HTML_Document';

$body = $view->body;

$body->append([
    'tag' => 'h2',
    'data' => '... world!',
    'attributes' => [
        'class' => 'class-name',
        'data-test' => 'value'
    ]
]);

$body->insert([
    'tag' => 'h1',
    'data' => 'Hello ...'
], 'h2');

$body->append([
    'tag' => 'input',
    'attributes' => [
        'type' => 'checkbox',
        'checked' => 'checked',
        'autocomplete' => true
    ]
]);

$body->append([
    'tag' => 'code',
    'data' => (string) $body->select('$body>input:checkbox:autocomplete:checked')
]);