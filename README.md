# Rolix

A secure and intuitive `currency` exchange platform designed to `facilitate` seamless transactions and promote peace of mind in financial exchanges.

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
