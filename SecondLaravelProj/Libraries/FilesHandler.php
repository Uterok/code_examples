<?php

namespace App\Libraries\Files;

class FilesHandler
{
    public static function getFileContents($path)
    {
        return file_exists($path) ? file_get_contents($path) : null;
    }

    public static function writeFile($path, $contents)
    {
        return (bool) file_put_contents($path, $contents, LOCK_EX);
    }

    public static function readAValuePairWithAKey(string $content, string $key, string $delimiter): ?string
    {
        // Match the given key at the beginning of a line
        if (preg_match("#^ *'{$key}' *{$delimiter} *[^\r\n]*$#uimU", $content, $matches)) {
            return $matches[0];
        }

        return null;
    }

    public static function returnValueFromFileByKey(string $path, string $name_file, string $key, string $delimiter): ?string
    {
        if (!file_exists($path . $name_file)) {
            return null;
        }

        $content = static::getFileContents($path . $name_file);

        $str = trim(static::readAValuePairWithAKey($content, $key, $delimiter));
        $arr = explode($delimiter, $str);

        $str_return =trim(trim($arr[1], "',"), " '");

        return $str_return;
    }

    public static function checkNestedFolders($base_folder_path, $nested_folders_array)
    {
        if (empty($base_folder_path) || !is_string($base_folder_path) || !is_array($nested_folders_array)) {
            return;
        }

        $folder_name_to_check = $base_folder_path;
        foreach ($nested_folders_array as $folder_name) {
            $folder_name_to_check .= DIRECTORY_SEPARATOR.$folder_name;
            if (!file_exists($folder_name_to_check)) {
                mkdir($folder_name_to_check);
            } elseif (!is_dir($folder_name_to_check)) {
                return;
            }
        }

        return;
    }
}
