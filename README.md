<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Project

Ini adalah project Sosial Media untuk komunitas saya, Project ini dibuat dengan Laravel brezee API sebagai server/backendnya.
Fitur-fitur yang disajikan dari project ini meliputi membuat postingan berupa album foto, membuat thread, memberi komentar, memberi like dan lain sebagainya.
Untuk mencoba silahkan kunjungi tautan ini https://shalltears.vercel.app/
Project ini juga sudah tercover feature test.

## How To Use
- composer install
- php artisan migrate:fresh
- php artisan queue:work
- php artisan schedule:run

## How To Run Feature Test
- php artisan test --filter feature

## Built With
- Laravel 10
- Vinka Laravel-hashids
- MySQL
- Laravel sanctum

## License
The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
