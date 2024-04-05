<?php
return [
    'translator' => [
        'locale' => APPLICATION_LOCALE,
        'translation_file_patterns' => [
            [
                'type' => \Laminas\I18n\Translator\Loader\PhpArray::class,
                'base_dir' => APPLICATION_PATH . '/../languages/' . APPLICATION_SITE,
                'pattern' => '%s.php',
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            \Laminas\I18n\Translator\TranslatorInterface::class => \Laminas\I18n\Translator\TranslatorServiceFactory::class,
        ]
    ],
];
?>