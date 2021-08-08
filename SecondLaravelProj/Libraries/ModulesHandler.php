<?php

namespace App\Libraries\Modules;

class ModulesHandler
{
    public const MODULES_CONFIG_FILE = 'erp-modules-config.json';
    public const CORE_MODULE_CONFIG_FILE = 'erp-core-config.json';
    public const ERP_MODULES_AUTHOR = 'erp';
    public const MODULES_CONFIG_LIST_KEY = 'modules';

    public const ERP_MODULES_ROOT_FOLDER = 'modules';
    public const ERP_MODULES_CONTAINER_FOLDER = 'ErpModules';

    public const MODULES_VENDOR_DEFAULT = 'Geekdevel';

    public const CORE_MODULE_NAME = 'Core';

    public const VERSION_ON_CREATE = 'v0.0.1';

    public const MODULES_NAMESPACE_PREFIX = 'Modules';
    public const MODULES_LIVEWIRE_COMPONENTS_SUFFIX = 'Http\Livewire';

    public static function getCoreComposerFilePath($dev = false)
    {
        $filename = $dev ? 'composer-dev.json' : 'composer.json';

        return base_path().DIRECTORY_SEPARATOR.$filename;
    }

    public static function getModuleComposerFilePath($module_name)
    {
        $module_folder_path = ModulesHandler::getModuleFolderPath($module_name);
        $module_composer_file_path = $module_folder_path.DIRECTORY_SEPARATOR.'composer.json';

        return $module_composer_file_path;
    }

    public static function prepareComposerDevToCommit()
    {
        $dev_filepath = static::getCoreComposerFilePath(true);
        $filepath = static::getCoreComposerFilePath();

        if (!file_exists($dev_filepath) || !file_exists($filepath)) {
            return [
                'success' => false,
                'error' => 'composer.json or composer-dev.json not exists'
            ];
        }

        $composer_file_content = json_decode(file_get_contents($filepath), true);
        $composer_dev_file_content = json_decode(file_get_contents($dev_filepath), true);
    }

    public static function getModulesRootFolderPath($base_path = null)
    {
        $base_path = $base_path ?? base_path();
        return $base_path.DIRECTORY_SEPARATOR.(static::ERP_MODULES_ROOT_FOLDER);
    }

    public static function getModulesFolderPath($base_path = null)
    {
        return static::getModulesRootFolderPath($base_path).DIRECTORY_SEPARATOR.(static::ERP_MODULES_CONTAINER_FOLDER);
    }

    public static function getModuleFolderPath($module_name, $base_path = null)
    {
        $base_path = $base_path ?? base_path();
        return static::getModulesFolderPath($base_path).DIRECTORY_SEPARATOR.$module_name;
    }

    public static function getModuleSeedersFolderPath($module_name, $base_path = null)
    {
        $base_path = $base_path ?? base_path();
        return static::getModuleFolderPath($module_name, $base_path).DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'seeders';
    }

    public static function getModulePackageName($module_name)
    {
        return static::ERP_MODULES_AUTHOR . '/' . camelCaseToKebabCase($module_name);
    }

    public static function getModuleRootNamespace($module_name)
    {
        return 'Modules\\' . $module_name;
    }

    public static function getCoreModuleConfigFilePath()
    {
        $core_module_config_file_path = app_path(static::CORE_MODULE_CONFIG_FILE);

        return $core_module_config_file_path;
    }

    public static function getModulesConfigFilePath()
    {
        $modules_folder_path = static::getModulesRootFolderPath();
        $modules_config_file_path = $modules_folder_path.DIRECTORY_SEPARATOR.(static::MODULES_CONFIG_FILE);

        return $modules_config_file_path;
    }

    public static function getModulesConfigFileContent()
    {
        $file_path = static::getModulesConfigFilePath();
        if (!file_exists($file_path)) {
            return null;
        }

        $content = json_decode(file_get_contents($file_path), true);

        return $content;
    }

    public static function isModuleExists($module_name)
    {
        $module_path = ModulesHandler::getModuleFolderPath($module_name);
        return file_exists($module_path);
    }

    public static function isModuleInUse($module_name)
    {
        if (!isModuleExists($module_name)) {
            return false;
        }

        $file_content = static::getModulesConfigFileContent();

        return $file_content['modules'][$module_name]['state'] ?? false;
    }

    public static function getModulesGitignorePath()
    {
        $modules_folder_path = static::getModulesFolderPath();
        $modules_folder_gitignore_path = $modules_folder_path.DIRECTORY_SEPARATOR.'.gitignore';

        return $modules_folder_gitignore_path;
    }

    public static function getCoreModuleConfigFileContent()
    {
        $core_module_config_file_path = static::getCoreModuleConfigFilePath();

        $core_module_config = file_exists($core_module_config_file_path) ?
                          json_decode(file_get_contents($core_module_config_file_path), true) :
                          [];

        return $core_module_config;
    }

    public static function getCoreModuleConfig()
    {
        return static::getCoreModuleConfigFileContent()["modules"][static::CORE_MODULE_NAME] ?? null;
    }

    public static function getModulesConfig()
    {
        $modules_config_file_path = static::getModulesConfigFilePath();

        $modules_config = file_exists($modules_config_file_path) ?
                          json_decode(file_get_contents($modules_config_file_path), true) :
                          [];

        return $modules_config;
    }

    public static function getModuleConfig($module_name = null)
    {
        $module_config = null;

        if (!empty($module_name)) {
            $modules_info = static::getModulesConfig($module_name);
            $module_config = $modules_info['modules'][$module_name] ?? null;
        } else {
            $module_config = static::getCoreModuleConfig();
        }

        return $module_config;
    }

    public static function setModuleConfig($module_name, $config = [])
    {
        $modules_config = static::getModulesConfig();
        $modules_list = $modules_config[static::MODULES_CONFIG_LIST_KEY] ?? [];
        $module_config_info = $modules_list[$module_name] ?? [];

        $module_config_info['name'] = $module_name ?? static::CORE_MODULE_NAME;
        $module_config_info['package_name'] = static::getModuleFullPackageName($module_name);
        $module_config_info['state'] = $config['state'] ?? false;
        $module_config_info['version'] = $config['version'] ?? static::VERSION_ON_CREATE;

        foreach ($config as $key => $value) {
            $module_config_info[$key] = $value;
        }

        $modules_list[$module_name] = $module_config_info;
        $modules_config[static::MODULES_CONFIG_LIST_KEY] = $modules_list;

        $modules_config_file_path = static::getModulesConfigFilePath();

        file_put_contents(
            $modules_config_file_path,
            json_encode($modules_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }

    public static function updateModuleConfig($config = [], $module_name = null)
    {
        $modules_config_content = null;
        $module_config_file_path = null;
        if (empty($module_name)) {
            $modules_config_content = static::getCoreModuleConfigFileContent();
            $module_name = static::CORE_MODULE_NAME;
            $module_config_file_path = static::getCoreModuleConfigFilePath();
        } else {
            $modules_config_content = static::getModulesConfig();
            $module_config_file_path = static::getModulesConfigFilePath();
        }

        $modules_list = $modules_config_content[static::MODULES_CONFIG_LIST_KEY] ?? [];
        $module_config_info = $modules_list[$module_name] ?? [];

        if (empty($module_config_info)) {
            return;
        }

        foreach ($config as $key => $value) {
            $module_config_info[$key] = $value;
        }

        $modules_config_content[static::MODULES_CONFIG_LIST_KEY][$module_name] = $module_config_info;

        file_put_contents(
            $module_config_file_path,
            json_encode($modules_config_content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }

    public static function deleteModuleConfig($module_name)
    {
        $modules_config = static::getModulesConfig();
        $modules_list = $modules_config[static::MODULES_CONFIG_LIST_KEY] ?? [];

        if (isset($modules_list[$module_name])) {
            unset($modules_list[$module_name]);
            $modules_config[static::MODULES_CONFIG_LIST_KEY] = $modules_list;

            $modules_config_file_path = static::getModulesConfigFilePath();

            file_put_contents(
                $modules_config_file_path,
                json_encode($modules_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                LOCK_EX
            );
        }
    }

    public static function deleteModuleFromModulesGitignoreFile($module_name)
    {
        $modules_folder_gitignore_path = static::getModulesGitignorePath();

        $gitignore_content = file_exists($modules_folder_gitignore_path) ?
                             file_get_contents($modules_folder_gitignore_path) :
                             '';

        if (
            !empty($gitignore_content) &&
            in_array($module_name, explode(PHP_EOL, $gitignore_content))
        ) {
            $regexp = '/' .PHP_EOL . '?' . $module_name .PHP_EOL . '?/';
            $gitignore_content = preg_replace($regexp, '', $gitignore_content);
        }

        file_put_contents(
            $modules_folder_gitignore_path,
            $gitignore_content,
            LOCK_EX
        );
    }

    public static function deleteExistedModule($module_name, $console = null, $composer_update = true)
    {
        $module_folder_path = static::getModuleFolderPath($module_name);

        if (file_exists($module_folder_path)) {
            delete_directory($module_folder_path);
        } else {
            if (!empty($console)) {
                $console->info("Module folder not exists.");
            }
        }

        static::deleteModuleConfig($module_name);

        static::deleteModuleFromModulesGitignoreFile($module_name);

        $out_array = [];
        // reload classes
        if ($composer_update) {
            exec(
                'COMPOSER_MEMORY_LIMIT=-1 composer update',
                $out_array
            );
        }

        return [
            'success' => true
        ];
    }

    public static function getModuleSeparatedPackageName($module_name = null)
    {
        $module_name = !empty($module_name) ? $module_name : config('modules.core_module_name');

        $module_repo_info_from_config = config('modules.repositories_info.'.$module_name);

        return [
            'vendor_name' => $module_repo_info_from_config['vendor_name'] ?? static::MODULES_VENDOR_DEFAULT,
            'package_name' => $module_repo_info_from_config['package_name'] ?? camelCaseToKebabCase($module_name),
        ];
    }

    public static function getModuleFullPackageName($module_name, $vendor_name = null, $package_name = null)
    {
        $separated_module_info = static::getModuleSeparatedPackageName($module_name);

        $vendor_name = (!empty($vendor_name) && is_string($vendor_name)) ? $vendor_name : $separated_module_info['vendor_name'];
        $package_name = (!empty($package_name) && is_string($package_name)) ? $package_name : $separated_module_info['package_name'];

        $module_repo_name = "${vendor_name}/${package_name}";

        return $module_repo_name;
    }
}
