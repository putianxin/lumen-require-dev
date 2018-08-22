<?php

namespace PtxDev;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Laravelista\LumenVendorPublish\VendorPublishCommand;

class PtxDevServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->setIdeHelperConfig();

        $this->app->register(\PtxDev\Swagger\SwaggerLumenServiceProvider::class);
        $this->app->register(\Jormin\DDoc\DDocServiceProvider::class);
        $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);

        // 添加命令
        $this->commands(
            VendorPublishCommand::class
        );
    }

    private function setIdeHelperConfig()
    {
        // 根据 composer autoload 获取 models 目录
        $composerSettings = json_decode(file_get_contents(base_path('composer.json')), true);
        $autoloadPsr4 = $composerSettings['autoload']['psr-4'];
        $modelDirs = [];
        foreach ($autoloadPsr4 as $dir) {
            $tmpDir = trim($dir, '/') . '/Models';
            if (File::exists(base_path($tmpDir))) {
                $modelDirs[] = $tmpDir;
            }
        }

        // 设置 model_locations
        $config = $this->app->make('config');
        $ideHelperConfig = $config->get('ide-helper', []);
        $modelLocations = $ideHelperConfig['model_locations'] ?? [];
        $modelLocations = array_merge($modelLocations, $modelDirs);
        $ideHelperConfig['model_locations'] = $modelLocations;
        $config->set('ide-helper', $ideHelperConfig);
    }
}
