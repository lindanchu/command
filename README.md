# Giới thiệu

Đây là 1 package tạo file reposiotory bằng command danh cho laravel

## Cài đặt

```bash
composer require linhdanchu/command
```

### Sử dụng

đăng ki Povider
```php
Linhdanchu\Artisan\Providers\RepositoryServicePovider::class
```

tạo file reposiotory `php artisan make:repository {composer} {suffix?} {base_repository?} {construct?} {create_base_repository?}`

| Parameter                 | Type      | Required  | Default   | Description |
| :------------------------ | :-------- | :-------- | :-------- | :---- |
| composer                  | string    | true      |           | Tên file repository
| suffix                    | boolean   | false     | true      | Trạng thái tên file repository đã có tên hậu tố Repository
| base_repository           | boolean   | false     | false     | Trạng thái extends BaseRepository
| construct                 | boolean   | false     | false     | Có hàm __construct
| create_base_repository    | boolean   | false     | false     | Trạng thái tạo file BaseRepository