<?php

namespace App\Libraries\Modules;

use App\Libraries\Modules\ModulesHandler;
use App\Libraries\Files\FilesHandler;
use Artisan;

class ModuleArtisanMaker
{
    public static function makeMigration($module_name, $make_string)
    {
        $check_result = static::checkBeforeMake($module_name, $make_string);

        if (!empty($check_result)) {
            return $check_result;
        }

        $parameters = static::separateNameParameter(static::getParametersFromString($make_string));

        if (empty($parameters['name'])) {
            return [
                'success' => false,
                'error' => 'Name not specified.'
            ];
        }

        $out_array = [];
        Artisan::call('make:migration', $parameters, $out_array);

        $migrations_folder_path = base_path().DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';
        $files_list = scandir($migrations_folder_path);

        if (empty($files_list) || !is_array($files_list)) {
            return [
                'success' => false,
                'error' => 'database/migrations folder empty or scanning error of this folder.'
            ];
        }

        $migration_filename_part = $parameters['name'];
        $regexp = '/[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_' . $migration_filename_part . '.php/';

        $module_migrations_path = ModulesHandler::getModuleFolderPath($module_name).DIRECTORY_SEPARATOR
                                  .'database'.DIRECTORY_SEPARATOR.'migrations';

        foreach ($files_list as $file_name) {
            if (preg_match($regexp, $file_name)) {
                $from = $migrations_folder_path.DIRECTORY_SEPARATOR.$file_name;
                $to = $module_migrations_path.DIRECTORY_SEPARATOR.$file_name;
                copy(
                    $from,
                    $to
                );
                unlink($from);
            }
        }

        return [
            'success' => true
        ];
    }

    public static function makeSeeder($module_name, $make_string)
    {
        $check_result = static::checkBeforeMake($module_name, $make_string);

        if (!empty($check_result)) {
            return $check_result;
        }

        $parameters = static::separateNameParameter(static::getParametersFromString($make_string));

        if (empty($parameters['name'])) {
            return [
                'success' => false,
                'error' => 'Name not specified.'
            ];
        }

        $out_array = [];
        Artisan::call('make:seeder', $parameters, $out_array);

        $seeders_folder_path = base_path().DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'seeders';
        $files_list = scandir($seeders_folder_path);

        if (empty($files_list) || !is_array($files_list)) {
            return [
                'success' => false,
                'error' => 'database/seeders folder empty or scanning error of this folder.'
            ];
        }

        $seeder_filename = $parameters['name'];
        $regexp = '/' . $seeder_filename . '.php/';

        $module_seeders_path = ModulesHandler::getModuleFolderPath($module_name).DIRECTORY_SEPARATOR
                               .'database'.DIRECTORY_SEPARATOR.'seeders';

        foreach ($files_list as $file_name) {
            if (preg_match($regexp, $file_name)) {
                $from = $seeders_folder_path.DIRECTORY_SEPARATOR.$file_name;
                $to = $module_seeders_path.DIRECTORY_SEPARATOR.$file_name;
                copy(
                    $from,
                    $to
                );
                unlink($from);
            }
        }

        return [
            'success' => true
        ];
    }

    public static function makeModel($module_name, $make_string)
    {
        $check_result = static::checkBeforeMake($module_name, $make_string);

        if (!empty($check_result)) {
            return $check_result;
        }

        $parameters = static::separateNameParameter(static::getParametersFromString($make_string));

        if (empty($parameters['name'])) {
            return [
                'success' => false,
                'error' => 'Name not specified.'
            ];
        }

        $splitted_name = explode('/', $parameters['name']);
        $model_classname = $splitted_name[count($splitted_name) - 1];

        $source_class_suffix = 'Module' . $module_name;
        $source_class_name = $model_classname . $source_class_suffix;
        $parameters['name'] = $source_class_name;

        $out_array = [];
        Artisan::call('make:model', $parameters, $out_array);

        $models_folder_path = base_path().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Models';
        $files_list = scandir($models_folder_path);

        if (empty($files_list) || !is_array($files_list)) {
            return [
                'success' => false,
                'error' => 'app/Models folder empty or scanning error of this folder.'
            ];
        }

        $regexp = '/' . $source_class_name . '.php/';

        $module_models_path = ModulesHandler::getModuleFolderPath($module_name).DIRECTORY_SEPARATOR
                              .'src'.DIRECTORY_SEPARATOR.'Models';

        foreach ($files_list as $file_name) {
            if (preg_match($regexp, $file_name)) {
                $from = $models_folder_path.DIRECTORY_SEPARATOR.$file_name;

                $folders_list = $splitted_name;
                array_splice($folders_list, -1);
                FilesHandler::checkNestedFolders($module_models_path, $folders_list);
                $path_to_copy_model = $module_models_path.DIRECTORY_SEPARATOR;
                if (!empty($folders_list)) {
                    $path_to_copy_model .= implode(DIRECTORY_SEPARATOR, $folders_list).DIRECTORY_SEPARATOR;
                }
                $path_to_copy_model .= $model_classname.'.php';
                $to = $path_to_copy_model;
                copy(
                    $from,
                    $to
                );
                unlink($from);

                if (file_exists($to)) {
                    $new_model_file_content = file_get_contents($to);

                    $new_model_file_content = str_replace($source_class_name, $model_classname, $new_model_file_content);
                    $module_root_namespace = ModulesHandler::getModuleRootNamespace($module_name);
                    $new_module_namespace = trim($module_root_namespace.'\\Models\\'.implode('\\', $folders_list), '\\');
                    $new_model_file_content = preg_replace(
                        '/namespace .*;/', 
                        "namespace ${new_module_namespace};", 
                        $new_model_file_content
                    );
                    FilesHandler::writeFile($to, $new_model_file_content);
                }
            }
        }

        return [
            'success' => true
        ];
    }

    public static function makeLivewire($module_name, $make_string)
    {
        $check_result = static::checkBeforeMake($module_name, $make_string);

        if (!empty($check_result)) {
            return $check_result;
        }

        $parameters = static::separateNameParameter(static::getParametersFromString($make_string));

        if (empty($parameters['name'])) {
            return [
                'success' => false,
                'error' => 'Name not specified.'
            ];
        }

        $splitted_name = explode('/', $parameters['name']);
        $livewire_classname = $splitted_name[count($splitted_name) - 1];

        $source_class_suffix = 'Module' . $module_name;
        $source_class_name = $livewire_classname . $source_class_suffix;
        $parameters['name'] = $source_class_name;

        $out_array = [];
        Artisan::call('make:livewire', $parameters, $out_array);

        // make component file
        $livewire_folder_path = base_path().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR
                                .'Http'.DIRECTORY_SEPARATOR.'Livewire';
        $files_list = scandir($livewire_folder_path);

        if (empty($files_list) || !is_array($files_list)) {
            return [
                'success' => false,
                'error' => 'app/Http/Livewire folder empty or scanning error of this folder.'
            ];
        }

        $regexp = '/' . $source_class_name . '.php/';

        $module_livewire_path = ModulesHandler::getModuleFolderPath($module_name).DIRECTORY_SEPARATOR.'src'
                                .DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Livewire';

        foreach ($files_list as $file_name) {
            if (preg_match($regexp, $file_name)) {
                $from = $livewire_folder_path.DIRECTORY_SEPARATOR.$file_name;

                $folders_list = $splitted_name;
                array_splice($folders_list, -1);
                FilesHandler::checkNestedFolders($module_livewire_path, $folders_list);
                $path_to_copy_livewire = $module_livewire_path.DIRECTORY_SEPARATOR;
                if (!empty($folders_list)) {
                    $path_to_copy_livewire .= implode(DIRECTORY_SEPARATOR, $folders_list).DIRECTORY_SEPARATOR;
                }
                $path_to_copy_livewire .= $livewire_classname.'.php';
                $to = $path_to_copy_livewire;
                copy(
                    $from,
                    $to
                );
                unlink($from);

                if (file_exists($to)) {
                    $new_livewire_file_content = file_get_contents($to);

                    $new_livewire_file_content = str_replace($source_class_name, $livewire_classname, $new_livewire_file_content);
                    $module_root_namespace = ModulesHandler::getModuleRootNamespace($module_name);
                    $new_module_namespace = trim($module_root_namespace.'\\Http\\Livewire\\'.implode('\\', $folders_list), '\\');
                    $new_livewire_file_content = preg_replace(
                        '/namespace .*;/', 
                        "namespace ${new_module_namespace};", 
                        $new_livewire_file_content
                    );

                    $component_view_address = camelCaseToKebabCase($module_name).'::livewire.';
                    $views_folder_list = array_map(function ($item) {
                        return strtolower($item);
                    }, $folders_list);
                    if (!empty($folders_list)) {
                        $component_view_address .= implode('.', $views_folder_list).'.';
                    }
                    $component_view_address .= camelCaseToKebabCase($livewire_classname);
                    $new_livewire_file_content = preg_replace(
                        '/return view(.*);/',
                        // "return view('$component_view_address');",
                        "return view('${component_view_address}');",
                        $new_livewire_file_content
                    );

                    FilesHandler::writeFile($to, $new_livewire_file_content);
                }
            }
        }

        // make view file
        $livewire_folder_path = base_path().DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR
                                .'views'.DIRECTORY_SEPARATOR.'livewire';
        $files_list = scandir($livewire_folder_path);

        if (empty($files_list) || !is_array($files_list)) {
            return [
                'success' => false,
                'error' => 'resources/views/livewire folder empty or scanning error of this folder.'
            ];
        }

        $regexp = '/' . camelCaseToKebabCase($source_class_name) . '.blade.php/';

        $module_views_path = ModulesHandler::getModuleFolderPath($module_name).DIRECTORY_SEPARATOR
                             .'resources'.DIRECTORY_SEPARATOR.'views';
        FilesHandler::checkNestedFolders($module_views_path, ['livewire']);
        $module_livewire_path = $module_views_path.DIRECTORY_SEPARATOR.'livewire';

        foreach ($files_list as $file_name) {
            if (preg_match($regexp, $file_name)) {
                \Log::info('MATCHED FILENAME - ' . $file_name);
                $from = $livewire_folder_path.DIRECTORY_SEPARATOR.$file_name;

                $folders_list = $splitted_name;
                array_splice($folders_list, -1);
                $folders_list = array_map(function ($item) {
                    return strtolower($item);
                }, $folders_list);
                FilesHandler::checkNestedFolders($module_livewire_path, $folders_list);
                $path_to_copy_livewire = $module_livewire_path.DIRECTORY_SEPARATOR;
                if (!empty($folders_list)) {
                    $path_to_copy_livewire .= implode(DIRECTORY_SEPARATOR, $folders_list).DIRECTORY_SEPARATOR;
                }
                $path_to_copy_livewire .= camelCaseToKebabCase($livewire_classname).'.blade.php';
                $to = $path_to_copy_livewire;
                copy(
                    $from,
                    $to
                );
                unlink($from);
            }
        }

        return [
            'success' => true
        ];
    }

    public static function makeController($module_name, $make_string)
    {
        $check_result = static::checkBeforeMake($module_name, $make_string);

        if (!empty($check_result)) {
            return $check_result;
        }

        $parameters = static::separateNameParameter(static::getParametersFromString($make_string));

        if (empty($parameters['name'])) {
            return [
                'success' => false,
                'error' => 'Name not specified.'
            ];
        }

        $splitted_name = explode('/', $parameters['name']);
        $controller_classname = $splitted_name[count($splitted_name) - 1];

        $source_class_suffix = 'Module' . $module_name;
        $source_class_name = $controller_classname . $source_class_suffix;
        $parameters['name'] = $source_class_name;

        $out_array = [];
        Artisan::call('make:controller', $parameters, $out_array);

        $controller_folder_path = base_path().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR
                                  .'Http'.DIRECTORY_SEPARATOR.'Controllers';
        $files_list = scandir($controller_folder_path);

        if (empty($files_list) || !is_array($files_list)) {
            return [
                'success' => false,
                'error' => 'app/Http/Controllers folder empty or scanning error of this folder.'
            ];
        }

        $regexp = '/' . $source_class_name . '.php/';

        $module_controllers_path = ModulesHandler::getModuleFolderPath($module_name).DIRECTORY_SEPARATOR
                                   .'src'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers';

        foreach ($files_list as $file_name) {
            if (preg_match($regexp, $file_name)) {
                $from = $controller_folder_path.DIRECTORY_SEPARATOR.$file_name;

                $folders_list = $splitted_name;
                array_splice($folders_list, -1);
                FilesHandler::checkNestedFolders($module_controllers_path, $folders_list);
                $path_to_copy_controller = $module_controllers_path.DIRECTORY_SEPARATOR;
                if (!empty($folders_list)) {
                    $path_to_copy_controller .= implode(DIRECTORY_SEPARATOR, $folders_list).DIRECTORY_SEPARATOR;
                }
                $path_to_copy_controller .= $controller_classname.'.php';
                $to = $path_to_copy_controller;
                copy(
                    $from,
                    $to
                );
                unlink($from);

                if (file_exists($to)) {
                    $new_controller_file_content = file_get_contents($to);

                    $new_controller_file_content = str_replace(
                        $source_class_name, 
                        $controller_classname, 
                        $new_controller_file_content
                    );
                    $module_root_namespace = ModulesHandler::getModuleRootNamespace($module_name);
                    $new_module_namespace = trim(
                        $module_root_namespace.'\\Http\\Controllers\\'.implode('\\', $folders_list), 
                        '\\'
                    );
                    $new_controller_file_content = preg_replace(
                        '/namespace .*;/', 
                        "namespace ${new_module_namespace};", 
                        $new_controller_file_content
                    );
                    $new_controller_file_content = str_replace(
                        'use Illuminate\Http\Request;', 
                        "use Illuminate\Http\Request;\r\nuse App\Http\Controllers\Controller;", 
                        $new_controller_file_content
                    );
                    FilesHandler::writeFile($to, $new_controller_file_content);
                }
            }
        }

        return [
            'success' => true
        ];
    }

    protected static function checkBeforeMake($module_name, $make_string)
    {
        if (empty($module_name) || !ModulesHandler::isModuleExists($module_name)) {
            return [
                'success' => false,
                'error' => 'Empty module name or module not exists.'
            ];
        }

        if (empty($make_string) || !is_string($make_string)) {
            return [
                'success' => false,
                'error' => 'Empty or wrong string to make.'
            ];
        }

        return false;
    }

    protected static function getParametersFromString($input_string)
    {
        $exploded = explode(' ', $input_string);

        if (!is_array($exploded)) {
            return [];
        }

        $filtered = array_filter($exploded, function ($item) {
            return !empty($item);
        });

        return array_values($filtered);
    }

    protected static function separateNameParameter($parameters)
    {
        $new_params_array = [];

        foreach ($parameters as $param) {
            if (preg_match('/^--.+/', $param)) {
                array_push($new_params_array, $param);
            } else {
                $new_params_array['name'] = $param;
            }
        }

        return $new_params_array;
    }
}
