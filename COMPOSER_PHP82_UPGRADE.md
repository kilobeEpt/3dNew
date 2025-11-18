# Composer & PHP 8.2 Compatibility Update

## Резюме изменений

Файл `composer.json` был обновлен для полной совместимости с PHP 8.2.28 и современными версиями пакетов.

## Установленные версии пакетов

### Основные зависимости (require):
- **PHP**: ^8.2 (минимум PHP 8.2.0, максимум < 9.0)
- **vlucas/phpdotenv**: ^5.6.2 (полная поддержка PHP 8.0+, включая 8.2 и 8.3)
- **phpmailer/phpmailer**: ^6.12.0 (поддержка PHP 5.5+ до 8.x)

### Зависимости для разработки (require-dev):
- **phpunit/phpunit**: ^9.6.29 (поддержка PHP 7.3+, совместим с PHP 8.2)

## Изменения в composer.json

```json
{
    "require": {
        "php": "^8.2",
        "vlucas/phpdotenv": "^5.6",
        "phpmailer/phpmailer": "^6.9"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.6"
    },
    "config": {
        "optimize-autoloader": true,
        "sort-packages": true,
        "platform": {
            "php": "8.2.28"
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

## КРИТИЧЕСКИ ВАЖНО: Проблема с Composer 1.9.0

⚠️ **Composer 1.9.0 устарел и НЕ МОЖЕТ скачивать пакеты с Packagist!**

Packagist.org отключил поддержку Composer 1.x в конце 2020 года. Это означает, что:

1. `composer install` НЕ БУДЕТ работать на сервере с Composer 1.9.0
2. `composer update` НЕ БУДЕТ работать на сервере с Composer 1.9.0
3. Невозможно скачать новые пакеты или обновить существующие

### Решения проблемы

#### ✅ РЕКОМЕНДУЕМОЕ РЕШЕНИЕ: Обновить Composer на хостинге

Обратитесь к вашему хостинг-провайдеру с запросом обновить Composer до версии 2.2 или выше.

**Как обновить Composer самостоятельно (если есть SSH доступ):**

```bash
# Проверить текущую версию
composer --version

# Обновить Composer до последней версии
composer self-update

# Или скачать Composer 2.x вручную
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/path/to/bin --filename=composer
php -r "unlink('composer-setup.php');"
```

#### ✅ АЛЬТЕРНАТИВНОЕ РЕШЕНИЕ #1: Использовать существующий composer.lock

Файл `composer.lock` был создан с использованием Composer 2.9.1 и содержит все необходимые зависимости.

**На локальной машине или CI/CD:**
```bash
# Сгенерирован composer.lock (УЖЕ СДЕЛАНО)
composer update --no-dev --optimize-autoloader
```

**На сервере с Composer 1.9.0:**
```bash
# Загрузить composer.lock на сервер
# Затем установить зависимости ИЗ ЛОКАЛЬНОГО КЕША или ZIP-архивов
composer install --no-dev --optimize-autoloader --prefer-dist
```

⚠️ **ВНИМАНИЕ:** Это может не сработать, если Composer 1.9.0 все равно попытается обратиться к Packagist.

#### ✅ АЛЬТЕРНАТИВНОЕ РЕШЕНИЕ #2: Vendor в репозитории

Если обновление Composer невозможно, добавьте папку `vendor/` в Git-репозиторий:

```bash
# 1. Установить зависимости локально
composer install --no-dev --optimize-autoloader

# 2. Удалить vendor/ из .gitignore
sed -i '/vendor/d' .gitignore

# 3. Добавить vendor в репозиторий
git add vendor/
git commit -m "Add vendor dependencies for deployment"
git push
```

**Плюсы:**
- Работает на любом хостинге без Composer
- Гарантированная совместимость версий

**Минусы:**
- Увеличение размера репозитория (~5-10 MB)
- Усложнение обновления зависимостей

#### ✅ АЛЬТЕРНАТИВНОЕ РЕШЕНИЕ #3: Загрузить vendor вручную

```bash
# 1. Локально установить зависимости
composer install --no-dev --optimize-autoloader

# 2. Создать архив vendor/
tar -czf vendor.tar.gz vendor/

# 3. Загрузить vendor.tar.gz на сервер через FTP/SFTP

# 4. На сервере распаковать
tar -xzf vendor.tar.gz
rm vendor.tar.gz
```

## Проверка совместимости

После установки зависимостей проверьте работоспособность:

```php
<?php
// test.php
require __DIR__ . '/vendor/autoload.php';

echo "✓ Autoloader works!\n";
echo "✓ PHP Version: " . PHP_VERSION . "\n";
echo "✓ PHPMailer version: " . PHPMailer\PHPMailer\PHPMailer::VERSION . "\n";

// Test Dotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
echo "✓ Dotenv loaded successfully!\n";
```

Запустите:
```bash
php test.php
```

Ожидаемый результат:
```
✓ Autoloader works!
✓ PHP Version: 8.2.28
✓ PHPMailer version: 6.12.0
✓ Dotenv loaded successfully!
```

## Команды для установки

### Для production (без dev-зависимостей):
```bash
composer install --no-dev --optimize-autoloader
```

### Для development (с PHPUnit и тестами):
```bash
composer install --optimize-autoloader
```

### Обновление зависимостей:
```bash
composer update --no-dev --optimize-autoloader
```

## Совместимость с PHP версиями

Текущий `composer.json` требует **PHP ^8.2** (то есть >= 8.2.0 и < 9.0).

Если нужна поддержка PHP 7.4 или 8.0/8.1:
```json
{
    "require": {
        "php": ">=7.4"
    }
}
```

Но для вашего хостинга с PHP 8.2.28 текущая конфигурация оптимальна.

## Зависимости и их версии

### vlucas/phpdotenv 5.6.2
- Загрузка переменных окружения из .env файла
- Поддержка PHP 8.0+
- [Документация](https://github.com/vlucas/phpdotenv)

### phpmailer/phpmailer 6.12.0
- Отправка email через SMTP
- Поддержка PHP 5.5 - 8.x
- [Документация](https://github.com/PHPMailer/PHPMailer)

### phpunit/phpunit 9.6.29
- Фреймворк для unit-тестирования
- Поддержка PHP 7.3+
- [Документация](https://phpunit.de/)

## Файлы в репозитории

После выполнения обновления:
- ✅ `composer.json` - обновлен для PHP 8.2
- ✅ `composer.lock` - сгенерирован с актуальными версиями
- ✅ `vendor/` - установленные зависимости (добавьте в .gitignore или репозиторий)

## Troubleshooting

### Ошибка: "The requested PHP extension ... is missing"
Установите необходимые PHP-расширения:
```bash
# Debian/Ubuntu
sudo apt-get install php8.2-mbstring php8.2-xml php8.2-curl

# CentOS/RHEL
sudo yum install php82-mbstring php82-xml php82-curl
```

### Ошибка: "Your requirements could not be resolved"
Проверьте версию PHP:
```bash
php -v
```

Должно быть >= 8.2.0.

### Ошибка: Composer не может скачать пакеты
Это означает, что у вас Composer 1.x. Обновите до Composer 2.x (см. выше).

## Контакты поддержки

Если у вас возникли проблемы:
1. Проверьте версию PHP: `php -v`
2. Проверьте версию Composer: `composer --version`
3. Убедитесь, что все необходимые расширения установлены: `php -m`

## Дополнительная информация

- [Composer Documentation](https://getcomposer.org/doc/)
- [PHP 8.2 Migration Guide](https://www.php.net/manual/en/migration82.php)
- [Packagist.org](https://packagist.org/)
