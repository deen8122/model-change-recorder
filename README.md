Model change recorder - пакет для хранения изменений в модели Laravel
===============

## Установка

```bash
composer require deen812/model-change-recorder
```

Для создания таблицы запустите миграцию:

```bash
php artisan migrate
```

## Использование

#### Добавление в метод модели

Когда происходит редактирование модели достаточно добавить класс ModelChangeRecorderEvents

```php
class Item extends Model
{
    public static function boot()
    {
        parent::boot();
        //Отслеживаем изменения модели
        self::observe(new ModelChangeRecorderEvents());
    }
}
```

Когда необходимо отслеживать изменения на уровне запросов, например:

```php
//Событие модели update не будет вызван
Item::query()->update(['price' => rand(8,888)]);
```

Добавьте следующий код:

```php
class Item extends Model
{
    public static function boot()
    {
        parent::boot();
        //Отслеживаем изменения модели
        self::observe(new ModelChangeRecorderEvents());
    }
    
    //Теперь обнводение через queryBuilder тоже будет отслеживаться
    public function newEloquentBuilder($query)
    {
        return new ModelChangeRecorderQueryBuilderDecorator($query);
    }
}
```

#### Запуск воркера

```bash
php artisan queue:listen --queue=model_change_recorder
```