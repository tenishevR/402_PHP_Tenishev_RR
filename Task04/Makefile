.PHONY: install

install:
	@echo "🔧 Установка зависимостей..."
	@composer install --no-interaction --prefer-dist --optimize-autoloader

	@echo "🔧 Копирование .env.example в .env..."
	@cp .env.example .env 2>/dev/null || echo ".env уже существует"

	@echo "🔧 Генерация ключа приложения..."
	@php artisan key:generate --no-interaction

	@echo "🔧 Создание базы данных..."
	@touch database/database.sqlite

	@echo "🔧 Выполнение миграций..."
	@php artisan migrate --force

	@echo "✅ Установка завершена! Запустите сервер: php artisan serve"