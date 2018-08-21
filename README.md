# Lumen 开发环境集成
## 注意事项
- windows环境下将 /dev/.htaccess 文件复制到 public 目录下面

## 常用命令
- swagger：API文档
    - 部署：php artisan swagger-lume:publish
    - json：php artisan swagger-lume:generate
- ddoc：数据库字典
    - 部署：php artisan vendor:publish
- ide-helper
    ````
    php artisan ide-helper:generate
    php artisan ide-helper:meta
    
    # 生成model注释
    php artisan ide-helper:models -W

## 集成模块
- barryvdh/laravel-ide-helper: 用于智能提示，生成`model`注释
- doctrine/dbal: 用于`laravel-ide-helper`生成`model`时使用
- laravelista/lumen-vendor-publish: 用于兼容命令 `php artisan vendor:publish`
- phan/phan: 用于静态代码检测
- ptx/laravel-db-doc: 用于查看数据库文档
- ptx/swagger-lumen-yaml: 用于写接口文档和测试用例
