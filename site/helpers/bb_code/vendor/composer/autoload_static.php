<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita27476581bb8d06eb39f1c012c2e81bf
{
    public static $prefixesPsr0 = array (
        'D' => 
        array (
            'Decoda' => 
            array (
                0 => __DIR__ . '/..' . '/mjohnson/decoda/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInita27476581bb8d06eb39f1c012c2e81bf::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
