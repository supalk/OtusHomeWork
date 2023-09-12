<?php

if (!function_exists('is_json')) {
    function is_json($string)
    {
        json_decode($string, true);

        return json_last_error() == JSON_ERROR_NONE;
    }
}

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        return rtrim(__DIR__,"/src") . "/{$path}";
    }
}

if (!function_exists('config_path')) {
    function config_path($path = '')
    {
        return base_path("config/{$path}");
    }
}

if (!function_exists('public_path')) {
    function public_path($path = '')
    {
        return base_path("public/{$path}");
    }
}

if (!function_exists('app_path')) {
    function app_path($path = '')
    {
        return base_path("src/{$path}");
    }
}

if (!function_exists('get_file_list')) {
    function get_file_list(string $directory, string $extension = ''): array
    {
        $filetype = '*';
        if (!empty($extension) && mb_substr($extension, 0, 1, 'UTF-8') != '.') {
            $filetype .= '.' . $extension;
        } else {
            $filetype .= $extension;
        }

        return glob($directory . DIRECTORY_SEPARATOR . $filetype);
    }
}

if (!function_exists('dd'))
{
    function dd()
    {
        array_map(function ($content) {
            echo "<pre>";
            var_dump($content);
            echo "</pre>";
            echo "<hr>";
        }, func_get_args());

        die;
    }
}
