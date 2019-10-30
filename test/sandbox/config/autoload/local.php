<?php

return [
    'options' => [
        \ExportBA\Options\AaOptions::class => [
            'options' => [
                'configurations' => [
                    'test' => [
                        'fetcher' => [\ExportBA\JobFetcher\OrganizationJobFetcher::class, ['id' => '5c518f7000c050654afc3f6b']],
                        'supplier_id' => 12345,
                        'partner_nr' => 54321,
                    ],
                ],
            ],
        ],
    ],
];