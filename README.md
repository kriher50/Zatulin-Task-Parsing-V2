# Zatulin-Task-Parsing-V2

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=flat-square)](https://livewire.laravel.com)

## 📝 Описание

Система агрегации новостей с поддержкой асинхронной загрузки и автоматического обновления контента.

![start](image/start.png)
![end](image/end.png)

## 🚀 Возможности

- ⚡ Асинхронная загрузка новостей
- 🔄 Автоматическое обновление контента
- 📱 Адаптивный дизайн
- 🔍 Фильтрация по источникам
- 🗑️ Soft Delete с возможностью восстановления
- 📊 Детальная статистика

## 🛠 Установка

### Быстрая установка

```bash
# Клонирование репозитория
git clone https://github.com/kriher50/Zatulin-Task-Parsing-V2.git
cd Zatulin-Task-Parsing-V2

# Установка зависимостей
composer install

# Копирование файла конфигурации
cp .env.example .env

# Генерация ключа приложения
php artisan key:generate

# Настройка базы данных в файле .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=your_database_name
# DB_USERNAME=root
# DB_PASSWORD=

# Выполнение миграций
php artisan migrate

# Установка Node.js зависимостей
npm install
# Для автоматического исправления Уязвимости.
npm audit fix

# Сборка фронтенд-ресурсов
npm run build

# Запуск очередей (в отдельном терминале)
php artisan queue:work

# Запуск планировщика задач (в отдельном терминале)
php artisan schedule:work

# Запуск сервера разработки
php artisan serve
```

## 🧪 Тестирование

```bash
# Загрузка новостей
php artisan news:fetch              # Загрузить последние новости с lenta.ru и ria.ru

# Управление удаленными новостями
php artisan news:check-deleted      # Просмотр статистики по активным и удаленным новостям
php artisan news:clear              # Мягкое удаление новостей с возможностью восстановления
php artisan news:force-delete       # Полное удаление новостей без возможности восстановления
php artisan news:restore            # Восстановление всех удаленных новостей

# Опции для восстановления новостей
php artisan news:restore --source=lenta  # Восстановить только новости Lenta.ru
php artisan news:restore --source=ria    # Восстановить только новости RIA.ru
php artisan news:restore --source=all    # Восстановить все новости
php artisan news:restore --id=123        # Восстановить конкретную новость по ID
```
