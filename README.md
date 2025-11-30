## Feed Server

Daily feed for interesting news and events

### Setup (Ubuntu):
```
apt install php-cli php-xml php-sqlite3
composer install 
cp .env.example .env
php artisan key generate
php artisan migrate
```

### Fetch today's items from various sources:
```
php artisan app:update
```

### Start dev:
```
php artisan serve
```




