<?php

namespace App\Libraries\ServiceContainer;

use App\Models\Settings\Setting;
use App\Models\Settings\Translation;
use App\Models\Settings\ContentSetting;

class SingletoneStorage
{
    public $default_language_settings;
    public $supported_language_settings;
    public $translations;
    public $settings;
    public $content_settings;

    public function __construct()
    {
        $this->loadToStorage();
    }

    public function loadToStorage()
    {
        $this->settings = Setting::all();
        $this->default_language_settings = ($this->settings->firstWhere('name', 'Default language')['values'])[0] ?? null;
        $this->supported_language_settings = ($this->settings->firstWhere('name', 'Supported languages')->first())['values'] ?? [];
        $this->translations = Translation::all();
        $this->content_settings = ContentSetting::first();
    }

    public function incrementCount()
    {
        $this->count++;
    }

    public function getSupportedLanguagesOrFallbackLang()
    {
        return (!empty($this->supported_language_settings) && is_array($this->supported_language_settings)) ?
                $this->supported_language_settings :
                (
                    !empty($this->default_language_settings) ?
                    [$this->default_language_settings] :
                    [config('app.locale')]
                );
    }
}
