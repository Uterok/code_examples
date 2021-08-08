<?php

namespace App\Libraries\Modules;

use App\Libraries\Files\FilesHandler;
use App\Libraries\Modules\ModulesHandler;
use Carbon\Carbon;

class NewModulesHandler
{
    public const CORE_MODULE_FOLDERS = [
        'src' => [
            'Http' => [
                'Controllers',
                'Livewire',
                'Middleware'
            ],
            'Libraries',
            'Models'
        ],
        'routes',
        'resources' => [
            'views',
        ],
        'config',
        'database' => [
            'migrations',
            'seeders',
            'factories'
        ]
    ];

    public static function setComposerFile($options)
    {
        $composer_file_content = [
            'name' => ModulesHandler::getModulePackageName($options['name']),
            'description' => $options['description'] ?? null,
            'type' => "library",
            'version' => ModulesHandler::VERSION_ON_CREATE,
            'license' => "MIT",
            'autoload' => [
                'psr-4' => [
                    'Modules\\' . $options['name'] . '\\' => 'src',
                    "Database\\Factories\\" => "database/factories/",
                    "Database\\Seeders\\" => "database/seeders/"

                ]
            ],
            'extra' => [
                'laravel' => [
                    'providers' => [
                        'Modules\\' . $options['name'] . '\\' . $options['name'] . 'ServiceProvider'
                    ]
                ]
            ]
        ];

        $module_folder_path = ModulesHandler::getModuleFolderPath($options['name']);
        $module_composer_file_path = $module_folder_path.DIRECTORY_SEPARATOR.'composer.json';

        file_put_contents(
            $module_composer_file_path,
            json_encode($composer_file_content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }

    public static function addModuleToIgnoreGit($module_name)
    {
        $modules_folder_gitignore_path = ModulesHandler::getModulesGitignorePath();

        $gitignore_content = file_exists($modules_folder_gitignore_path) ?
                             file_get_contents($modules_folder_gitignore_path) :
                             '';

        if (
            empty($gitignore_content) ||
            !in_array($module_name, explode(PHP_EOL, $gitignore_content))
        ) {
            $module_name_prefix = empty($gitignore_content) ? '' : PHP_EOL;

            $gitignore_content .= $module_name_prefix.$module_name;

            file_put_contents(
                $modules_folder_gitignore_path,
                $gitignore_content,
                LOCK_EX
            );
        }
    }

    public static function addModuleRepoToCoreComposerFile($module_name)
    {
        $composer_local_file_path = base_path().DIRECTORY_SEPARATOR.'composer-dev.json';

        $composer_file_content = null;
        if (!file_exists($composer_local_file_path)) {
            $composer_file_content = [];
        } else {
            $composer_file_content = json_decode(file_get_contents($composer_local_file_path), true);
        }

        // set repositories info
        $repositories_info = [];
        if (isset($composer_file_content['repositories']) && is_array($composer_file_content['repositories'])) {
            $repositories_info = $composer_file_content['repositories'];
        }

        $module_folder_path = ModulesHandler::getModuleFolderPath($module_name, '.');

        $existed_repository_info = array_filter($repositories_info, function ($item) use ($module_folder_path) {
            return !empty($item['url']) && ($item['url'] == $module_folder_path);
        });

        if (!empty($existed_repository_info)) {
            return;
        }

        $repositories_info[] = [
            'type' => 'path',
            "url" => $module_folder_path
        ];

        $composer_file_content['repositories'] = $repositories_info;

        file_put_contents(
            $composer_local_file_path,
            json_encode($composer_file_content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }

    public static function createNewModule($module_name, $options = [], $console = null)
    {
        static::createErpModulesFolderIfNotExists();

        $module_path = ModulesHandler::getModuleFolderPath($module_name);

        $instal_stubs_path = static::getModuleInstallStubsPath();

        if (file_exists($module_path)) {
            return [
                'success' => false,
                'error' => 'Folder with module name already exists'
            ];
        }

        $module_path_created = mkdir($module_path);

        if (!$module_path_created) {
            return [
                'success' => false,
                'error' => 'Cannot create module folder'
            ];
        }

        static::createCoreModuleFolders($module_path);

        $replaceFileContent = function ($path_to_file, $callback) {
            if (!is_callable($callback) || !file_exists($path_to_file)) {
                return false;
            }

            $file_content = file_get_contents($path_to_file);

            $replaced_content = $callback($file_content);

            file_put_contents($path_to_file, $replaced_content, LOCK_EX);

            return true;
        };

        $module_provider_filepath = $module_path.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.$module_name.'ServiceProvider.php';
        $success_copy = copy(
            $instal_stubs_path.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'ModuleServiceProvider.php',
            $module_provider_filepath
        );
        chmod($module_provider_filepath, 0755);

        $replaceFileContent(
            $module_provider_filepath,
            function ($content_to_replace) use ($module_name) {
                $content = str_replace('###module_name_kebab###', camelCaseToKebabCase($module_name), $content_to_replace);
                $content = str_replace(
                    'DummyServiceProviderClassName',
                    $module_name.'ServiceProvider',
                    $content
                );
                $content = str_replace(
                    'DummyModuleName',
                    $module_name,
                    $content
                );

                return $content;
            }
        );

        $web_routes_path = $module_path.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php';
        $success_copy = copy(
            $instal_stubs_path.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php',
            $web_routes_path
        );
        chmod($web_routes_path, 0755);

        $api_routes_path = $module_path.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'api.php';
        $success_copy = copy(
            $instal_stubs_path.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'api.php',
            $api_routes_path
        );
        chmod($api_routes_path, 0755);

        $module_config_filepath = $module_path.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'module.php';
        $success_copy = copy(
            $instal_stubs_path.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'module.php',
            $module_config_filepath
        );
        chmod($module_config_filepath, 0755);

        $replaceFileContent(
            $module_config_filepath,
            function ($content_to_replace) use ($module_name) {
                $content = str_replace('###route_prefix###', camelCaseToKebabCase($module_name), $content_to_replace);
                $content = str_replace('###web_default_middleware###', 'web', $content);
                $content = str_replace('###api_default_middleware###', 'api', $content);
                $content = str_replace('###module_name###', $module_name, $content);
                return $content;
            }
        );

        $gitignore_fiel_path = $module_path.DIRECTORY_SEPARATOR.'.gitignore';
        $success_copy = copy(
            $instal_stubs_path.DIRECTORY_SEPARATOR.'git_ignore',
            $gitignore_fiel_path
        );
        chmod($gitignore_fiel_path, 0755);

        static::setComposerFile([
            'name' => $module_name,
            'description' => $options['description'] ?? ''
        ]);

        // create env files
        $env_file_path = $module_path.DIRECTORY_SEPARATOR.'.env';
        if (!file_exists($env_file_path)) {
            file_put_contents($env_file_path, '', LOCK_EX);
            chmod($env_file_path, 0755);
        }

        $env_example_file_path = $module_path.DIRECTORY_SEPARATOR.'.env.example';
        if (!file_exists($env_example_file_path)) {
            file_put_contents($env_example_file_path, '', LOCK_EX);
            chmod($env_example_file_path, 0755);
        }

        static::addModuleToIgnoreGit($module_name);

        ModulesHandler::setModuleConfig(
            $module_name,
            [
                'added_at' => Carbon::now()->toDateTimeString()
            ]
        );

        $out_array = [];

        // reload classes
        exec(
            'COMPOSER_MEMORY_LIMIT=-1 composer update',
            $out_array
        );


        return [
            'success' => true
        ];
    }

    private static function getModuleInstallStubsPath()
    {
        return base_path().DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'install-stubs';
    }

    private static function createErpModulesFolderIfNotExists()
    {
        $modules_folder_path = base_path().DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'ErpModules';

        if (!file_exists($modules_folder_path)) {
            mkdir($modules_folder_path);
            chmod($modules_folder_path, 0755);
        }
    }

    private static function createCoreModuleFolders($folder_base_path, $inner_folders = null)
    {
        $folders_list = empty($inner_folders) ? static::CORE_MODULE_FOLDERS : $inner_folders;

        $createFolder = function ($path) {
            if (file_exists($path)) {
                return;
            }

            mkdir($path);
            file_put_contents($path.DIRECTORY_SEPARATOR.'.gitignore', '');
        };

        foreach ($folders_list as $key => $value) {
            if (is_array($value)) {
                $inner_folder_base_path = $folder_base_path.DIRECTORY_SEPARATOR.$key;
                $createFolder($inner_folder_base_path);
                static::createCoreModuleFolders($inner_folder_base_path, $value);
            } elseif (is_string($value) || is_numeric($value)) {
                $inner_folder_base_path = $folder_base_path.DIRECTORY_SEPARATOR.$value;
                $createFolder($inner_folder_base_path);
            }
        }
    }
}
