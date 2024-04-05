<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit876bb3bfe0460c95300cf8b77c05dd0b
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Models\\' => 7,
        ),
        'A' => 
        array (
            'Application\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Models\\' => 
        array (
            0 => __DIR__ . '/../..' . '/../../Models',
        ),
        'Application\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Application/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit876bb3bfe0460c95300cf8b77c05dd0b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit876bb3bfe0460c95300cf8b77c05dd0b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit876bb3bfe0460c95300cf8b77c05dd0b::$classMap;

        }, null, ClassLoader::class);
    }
}
