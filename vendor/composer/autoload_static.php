<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitcaf780fdd3a2b272fe095aa4dc83abd7
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Axdron\\Radianti\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Axdron\\Radianti\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitcaf780fdd3a2b272fe095aa4dc83abd7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitcaf780fdd3a2b272fe095aa4dc83abd7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitcaf780fdd3a2b272fe095aa4dc83abd7::$classMap;

        }, null, ClassLoader::class);
    }
}
