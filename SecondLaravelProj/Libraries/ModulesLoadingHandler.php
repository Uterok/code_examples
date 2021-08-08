<?php

namespace App\Libraries\Modules;

use App\Libraries\Modules\ModulesHandler;
use App\Libraries\Files\FilesHandler;
use Livewire;

class ModulesLoadingHandler
{
    public const LIVEWIRE_SUBFOLDER_PATH = 'src'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Livewire';

    public static function loadModulesProviders($app)
    {
        $modules_folder_path = ModulesHandler::getModulesFolderPath();

        if (!file_exists($modules_folder_path) || !is_dir($modules_folder_path)) {
            return;
        }

        $modules_subdirs_list = glob($modules_folder_path.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);

        //ON and OFF modules
        $modules_config = ModulesHandler::getModulesConfig()['modules'] ?? [];

        foreach ($modules_subdirs_list as $module_subdir) {
            $name = explode('/', $module_subdir)[count(explode('/', $module_subdir)) - 1];

            if (!empty($modules_config[$name]['state'])) {
                //checking the status of the module - enabled or disabled
                $module_composer_path = ModulesHandler::getModuleComposerFilePath($name);

                if (file_exists($module_composer_path)) {
                    $composer = json_decode(FilesHandler::getFileContents($module_composer_path), true);

                    $providers = $composer['extra']['laravel']['providers'] ?? null;
                    $aliases = $composer['extra']['laravel']['aliases'] ?? null;

                    if (!empty($providers)) {
                        foreach ($providers as $provider) {
                            $provider = '\\' . $provider;
                            if (is_string($provider) && class_exists($provider)) {
                                $app->register(new $provider($app));
                            }
                        }
                    }

                    if (!empty($aliases)) {
                        foreach ($aliases as $alias_name => $aliase) {
                            $aliase = '\\' . $aliase;
                            if (is_string($aliase) && class_exists($aliase)) {
                                $app->alias($alias_name, $aliase);
                            }
                        }
                    }
                }
            }
        }
    }

    public static function loadModulesLivewireComponents($module_name)
    {
        $module_folder_path = ModulesHandler::getModulesFolderPath();
        $livewire_path = $module_folder_path.DIRECTORY_SEPARATOR.(static::LIVEWIRE_SUBFOLDER_PATH);

        $classes = get_declared_classes();

        $livewire_prefix_regexp = ModulesHandler::MODULES_NAMESPACE_PREFIX . 
                                  '\\' . $module_name . '\\' . 
                                  ModulesHandler::MODULES_LIVEWIRE_COMPONENTS_SUFFIX . '\\';

        $module_livewire_classes = array_filter(
            $classes,
            function ($classname) use ($module_name, $livewire_prefix_regexp) {
                $regexp = '/^' . $livewire_prefix_regexp . '.*/';
                $regexp = str_replace('\\', '\\\\', $regexp);
                return preg_match($regexp, $classname);
            }
        );

        $modules_namespaces_to_register = array_map(
            function ($item) use ($livewire_prefix_regexp) {
                $only_livewire_namespaces = str_replace($livewire_prefix_regexp, '', $item);
                $splitted = explode('\\', $only_livewire_namespaces);
                $kebabed = array_map(
                    function ($innerest_item) {
                        return camelCaseToKebabCase($innerest_item);
                    },
                    $splitted
                );
                return [
                    'class' => $item,
                    'kebabed_component_name' => implode('.', $kebabed)
                ];
            },
            $module_livewire_classes
        );

        $kebabed_module_name = camelCaseToKebabCase($module_name);

        foreach ($modules_namespaces_to_register as $livewire_class_info) {
            $class_name = $livewire_class_info['class'];
            $kebabed_component_name = $livewire_class_info['kebabed_component_name'];
            $livewire_component_name_to_register = "modules.${kebabed_module_name}.${kebabed_component_name}";
            Livewire::component($livewire_component_name_to_register, $class_name);
        }
    }
}
