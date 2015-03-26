<?php
require_once 'DOM/HTML/Document.php';

(new DOM_HTML_Document(true))
->select('body')
    ->append([
        'tag' => 'form'
    ])
        ->append([
            'tag' => 'input',
            'attributes' => [
                'type' => 'text'
            ],
            'value' => 'an escaped value "\''
        ]);