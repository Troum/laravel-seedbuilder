# 🧩 Laravel SeedBuilder

**Laravel SeedBuilder** — это расширяемый, декларативный и SOLID-ориентированный компонент для Laravel, который позволяет создавать таблицы и сидировать данные напрямую из JSON или массива, без использования моделей и команд `db:seed`.

---

## 🚀 Возможности

✅ Автоматическое создание таблиц по структуре данных или по декларативной схеме  
✅ Вставка сидов напрямую из JSON или массива  
✅ Поддержка:
- `nullable`, `default`, `unique`, `primary`, `enum`, `foreign key`
- `Faker`-функций в `defaults`  
✅ Можно использовать в миграциях (`up()`) без сидеров  
✅ Совместим с Laravel 9–11 и PHP 8.1+

---

## 📦 Установка

1. Установи пакет через Composer:

```bash
composer require troum/laravel-seedbuilder
```

Или добавь репозиторий вручную:

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/Troum/laravel-seedbuilder"
  }
]
```

---

## 📁 Структура JSON

```json
{
  "table": "test_table",
  "schema": {
    "id": { "type": "bigIncrements", "primary": true },
    "login": { "type": "string", "unique": true },
    "password": { "type": "string" },
    "status": { "type": "enum", "values": ["active", "inactive"], "default": "active" },
    "created_at_dt": { "type": "timestamp", "default": "CURRENT_TIMESTAMP" }
  },
  "defaults": {
    "created_at_dt": "now",
    "deleted_is": "no"
  },
  "rows": [
    {
      "login": "login",
      "password": "password",
      "status": "active"
    }
  ]
}
```

✅ `schema` не требуется, если таблица уже существует  
✅ `defaults` применяются только к полям, не указанным в `rows`

---

## 🧪 Использование

### Через JSON

```php
use SeedBuilder\SeedBuilder;

SeedBuilder::insert('seeds/some_test_data.json');
```

### Через массив

```php
SeedBuilder::insert([
  'table' => 'users',
  'schema' => [
    'id' => ['type' => 'bigIncrements', 'primary' => true],
    'email' => ['type' => 'string', 'unique' => true]
  ],
  'rows' => [
    ['email' => 'user@example.com']
  ],
  'defaults' => [
    'created_at' => fn() => now(),
    'status' => 'active'
  ]
]);
```

---

## ⚙️ Поддерживаемые атрибуты полей

| Атрибут    | Назначение                                             |
|------------|--------------------------------------------------------|
| `type`     | Тип поля (Laravel-style: `string`, `timestamp`, ...)  |
| `nullable` | Явно разрешает NULL                                    |
| `default`  | Значение по умолчанию                                  |
| `primary`  | Делает поле первичным ключом                           |
| `unique`   | Устанавливает уникальный индекс                        |
| `enum`     | Перечисление (используй с `values`)                    |
| `foreign`  | Внешний ключ: `{ "table": "stores", "column": "id" }` |

---

## 📦 Пример использования в миграции

```php
use Illuminate\Database\Migrations\Migration;
use SeedBuilder\SeedBuilder;

return new class extends Migration {
    public function up(): void
    {
        SeedBuilder::insert('seeds/some_test_data.json');
    }

    public function down(): void
    {
        //
    }
};
```

---

## 📌 Поведение по умолчанию

| Поведение                                    | Описание                                                              |
|---------------------------------------------|-----------------------------------------------------------------------|
| Таблица существует                          | `schema` игнорируется, выполняется только вставка данных              |
| Таблица не существует + есть `schema`       | Создаётся таблица по описанию                                         |
| Таблица не существует + нет `schema`        | Попытка определить типы из первой строки `rows`                       |
| Нет данных и схемы                          | Выбрасывается исключение                                              |

---

## 🔧 План развития

- ✅ CLI-команда `php artisan seedbuilder:generate`
- ✅ Автоэкспорт таблицы в JSON
- ✅ Поддержка `faker:` шаблонов в JSON
- ✅ Автогенерация `created_at`, `updated_at` при необходимости

---

## 📝 Лицензия

Пакет распространяется под лицензией [MIT](LICENSE).
