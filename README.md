Model change recorder - пакет для хранения изменений в модели Laravel
===============

## Установка
```bash
composer require deen812/model-change-recorder
```
Для создания таблицы где буду хранится данные запустите миграцию:
```bash
php artisan migrate
```
## Использование

#### Добавление в метод модели
```php
class Item extends Model
{
    public static function boot()
    {
        parent::boot();
        self::observe(new ModelChangeRecorderEvents());
    }
}
```
#### Запуск воркера
```bash
php artisan queue:listen --queue=change_tracker
```