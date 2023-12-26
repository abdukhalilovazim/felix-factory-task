## System requirement

- PHP (8.1)
- MySQL (8.0)

----------------------------------------------------------------

## Configurate Project

### Download all packages using this command
```
composer update
```
### Copy .env file
```
cp .env.example .env
```
### Key generate
```
php artisan key:generate
```

### Run Project
```
php artisan serve
```

### Request url for check task
```
    POST:   http://127.0.0.1:8000/api/order
```

### Send this JSON to the URL
```
{
    "data": [
        {
            "product_id": 1, // Ko'ylak
            "qty": 30
        },
        {
            "product_id": 2, // Shim
            "qty": 20
        }
    ]
}

```
