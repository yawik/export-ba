<?php

/**
 * YAWIK Export BA
 * Main module configuration file
 *
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA;

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'doctrine' => [
        'driver' => [
            'odm_default' => [
                'drivers' => [
                    'ExportBA\Entity' => 'annotation'
                ]
            ],
            'annotation' => [
                'paths' => [
                    __DIR__ . '/../src/Entity'
                ]
            ]
        ]
    ],

    'console' => [
        'router' => [
            'routes' => [
                'export-ba:upload' => [
                    'options' => [
                        'route' => 'export-ba upload <name>',
                        'defaults' => [
                            'controller' => Controller\UploadConsoleController::class,
                            'action' => 'index',
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            JobFetcher\OrganizationJobFetcher::class => JobFetcher\OrganizationJobFetcherFactory::class,
            Client\AaClient::class => Client\AaClientFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\UploadConsoleController::class => Controller\UploadConsoleControllerFactory::class,
        ],
    ],

    'controller_plugins' => [
        'factories' => [
            Controller\Plugin\JobFetcher::class => Controller\Plugin\JobFetcherFactory::class,
            Controller\Plugin\AaXml::class => Controller\Plugin\AaXmlFactory::class,
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'export-ba/default' => __DIR__ . '/../views/default.phtml',
        ],
    ],

    'view_helpers' => [
        'factories' => [
            ViewHelper\TitleCode::class => InvokableFactory::class,
        ],
        'aliases' => [
            'titleCode' => ViewHelper\TitleCode::class,
            'titlecode' => ViewHelper\TitleCode::class,
        ]
    ],

    'options' => [
        Options\AaOptions::class => [
            'options' => [
                'configurations' => [
                    'aerztekammer' => [
                        'fetcher' => [JobFetcher\OrganizationJobFetcher::class, ['id' => 1234]],
                    ],
                ],
            ],
        ],
    ],

];
