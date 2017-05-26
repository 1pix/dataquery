<?php

return [
        'tx_dataquery_querycheckwizard' => [
                'path' => '/dataquery/querycheckwizard',
                'target' => \Tesseract\Dataquery\Ajax\AjaxHandler::class . '::validateAction'
        ]
];
