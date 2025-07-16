# Rolix

An advanced role, membership, and delegation system for Laravel — built for large-scale, multi-tenant, and modular applications.
It provides a robust solution for managing roles, permissions, and user memberships, allowing for flexible delegation of responsibilities across various modules.

## Install via composer

Run the following command to pull in the latest version:

```bash
composer require jobmetric/rolix
```

### Publish the config
Copy the `config` file from `vendor/jobmetric/rolix/config/config.php` to `config` folder of your Laravel application and rename it to `rolix.php`

Run the following command to publish the package config file:

```bash
php artisan vendor:publish --provider="JobMetric\Rolix\RolixServiceProvider" --tag="rolix-config"
```

You should now have a `config/rolix.php` file that allows you to configure the basics of this package.

## Documentation

coming soon...
