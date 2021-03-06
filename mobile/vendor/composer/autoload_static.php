<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit04174e4966cda507d0dfd648df7bfe72
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        '3b5531f8bb4716e1b6014ad7e734f545' => __DIR__ . '/..' . '/illuminate/support/Illuminate/Support/helpers.php',
        'afd7a91ad0e784ba0598a693675040e7' => __DIR__ . '/..' . '/topthink/thinkphp/ThinkPHP.php',
        'f68cc9c2f586bfcd149d99108550a34b' => __DIR__ . '/..' . '/topthink/thinkphp/helper.php',
        '5e6fd2917734bf20027a5c75eb8ceb2d' => __DIR__ . '/../..' . '/config/constant.php',
    );

    public static $prefixLengthsPsr4 = array (
        'e' => 
        array (
            'ectouch\\' => 8,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Component\\Translation\\' => 30,
            'Symfony\\Component\\Filesystem\\' => 29,
        ),
        'O' => 
        array (
            'Overtrue\\Pinyin\\' => 16,
            'OSS\\' => 4,
        ),
        'C' => 
        array (
            'Carbon\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ectouch\\' => 
        array (
            0 => __DIR__ . '/..' . '/ectouch/dagger/src',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Component\\Translation\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/translation',
        ),
        'Symfony\\Component\\Filesystem\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/filesystem',
        ),
        'Overtrue\\Pinyin\\' => 
        array (
            0 => __DIR__ . '/..' . '/overtrue/pinyin/src',
        ),
        'OSS\\' => 
        array (
            0 => __DIR__ . '/../..' . '/../plugins/aliyunoss/src/OSS',
        ),
        'Carbon\\' => 
        array (
            0 => __DIR__ . '/..' . '/nesbot/carbon/src/Carbon',
        ),
    );

    public static $prefixesPsr0 = array (
        'R' => 
        array (
            'Raven_' => 
            array (
                0 => __DIR__ . '/..' . '/sentry/sentry/lib',
            ),
        ),
        'P' => 
        array (
            'PHPExcel' => 
            array (
                0 => __DIR__ . '/..' . '/phpoffice/phpexcel/Classes',
            ),
        ),
        'I' => 
        array (
            'Illuminate\\Support' => 
            array (
                0 => __DIR__ . '/..' . '/illuminate/support',
            ),
            'Illuminate\\Events' => 
            array (
                0 => __DIR__ . '/..' . '/illuminate/events',
            ),
            'Illuminate\\Database' => 
            array (
                0 => __DIR__ . '/..' . '/illuminate/database',
            ),
            'Illuminate\\Container' => 
            array (
                0 => __DIR__ . '/..' . '/illuminate/container',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit04174e4966cda507d0dfd648df7bfe72::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit04174e4966cda507d0dfd648df7bfe72::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit04174e4966cda507d0dfd648df7bfe72::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
