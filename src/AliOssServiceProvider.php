<?php

namespace OliveFM\AliOSS;

use OliveFM\AliOSS\Plugins\PutFile;
use OliveFM\AliOSS\Plugins\PutRemoteFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use OSS\OssClient;

class AliOssServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //发布配置文件
        /*
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/config/config.php' => config_path('alioss.php'),
            ], 'config');
        }
        */

        Storage::extend('oss', function($app, $config)
        {
            $accessId  = $config['access_id'];
            $accessKey = $config['access_key'];
            $endPoint  = $config['endpoint']; // 默认作为外部节点
            $epInternal= empty($config['endpoint_internal']) ? $endPoint : $config['endpoint_internal']; // 内部节点
            $cdnDomain = empty($config['cdnDomain']) ? '' : $config['cdnDomain'];
            $bucket    = $config['bucket'];
            $ssl       = empty($config['ssl']) ? false : $config['ssl'];
            $isCName   = empty($config['isCName']) ? false : $config['isCName'];
            $debug     = empty($config['debug']) ? false : $config['debug'];
            $securityToken = empty($config['securityToken']) ? null : $config['securityToken'];
            //if($debug) Log::debug('OSS config:', $config);

            //修复上传使用cdn自定义域名报错的问题
            //解决方案:上传使用阿里云节点,只有显示使用自定义域名
            $client  = new OssClient($accessId, $accessKey, $epInternal, false, $securityToken);
            $adapter = new AliOssAdapter($client, $bucket, $endPoint, $ssl, $isCName, $debug, $cdnDomain);

            //Log::debug($client);
            $filesystem =  new Filesystem($adapter);
            
            $filesystem->addPlugin(new PutFile());
            $filesystem->addPlugin(new PutRemoteFile());
            //$filesystem->addPlugin(new CallBack());
            return $filesystem;
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }

}
