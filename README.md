# DashLog

A request monitoring and logging package for Laravel.

## Installation

```bash
composer require aleddev/dashlog
```

## Configuration

```bash
php artisan vendor:publish --provider="AledDev\DashLog\Infrastructure\Providers\DashLogServiceProvider"
```

## Middleware

```bash
php artisan vendor:publish --provider="AledDev\DashLog\Infrastructure\Providers\DashLogServiceProvider"
```

## Migrations

```bash
php artisan migrate
```

## Usage

- Now you have a middleware that will log the request and response, you can use it in the routes that you want to monitor.
- To access the dashboard, you can go to `your-domain.com/dashlog` and see the requests that have been made. ( We recommend you to have a middleware for this route )


## Recomendations 

- Use the middleware in the routes that you want to monitor.
- Configure the routes that you want to monitor in the `config/dashlog.php` file.
- If you want to make use of the AI analysis, you need to configure the `AIML_API_KEY` in the `.env` file. 

## AI Analysis

The AI analysis is optional and is not enabled by default. If you want to make use of the AI analysis, you need to configure the `AIML_API_KEY` in the `.env` file. 
We use the [AIML API](https://aiml.com/) to analyze the request logs, because it offers a free plan for low requests, if you want to use another provider, you will need to implement your own.

