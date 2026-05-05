# 📋 Changelog

All notable changes to `josrom/laravel-trello-wrapper` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [13.0.0] - 2026-05-05

### 🚀 Added
- ✨ Support for **Laravel 13**.
- 🐘 Minimum PHP version raised to **8.3**.
- 🎨 `friendsofphp/php-cs-fixer` as a dev dependency to enforce a consistent code style.
- 🧰 Composer scripts: `composer test`, `composer cs-check`, `composer cs-fix`.
- 📄 `.php-cs-fixer.dist.php` configuration file (PSR-12 + sensible defaults).
- 📝 `CHANGELOG.md` to track project changes.

### 🔧 Changed
- 📦 Bumped `illuminate/contracts` and `illuminate/support` to `^11.0 || ^12.0 || ^13.0`.
- 🤖 Rewrote GitHub Actions CI workflow: now uses `shivammathur/setup-php` with a matrix on PHP 8.3 and 8.4, and runs PHP-CS-Fixer in dry-run mode.
- 📚 Updated `README.md` to reference Laravel 13, PHP 8.3 and the new development tooling.
- 🧹 Applied PHP-CS-Fixer formatting across the codebase.

## [12.0.1] - 2025-05-05

### 🔧 Changed
- 📦 Updated PHP Trello API package dependency.

## [12.0.0] - 2025-03-11

### 🚀 Added
- ✨ Support for **Laravel 12**.

## [11.0.1] - 2025-03-11

### 🔧 Changed
- 📦 Updated `josrom/php-trello-api` to `^3.0`.

## [11.0.0]

### 🚀 Added
- ✨ Support for **Laravel 11** (PHP 8.2 or higher).

## [10.0.0]

### 🚀 Added
- ✨ Support for **Laravel 10** (PHP 8.1 or higher).

## [9.0.0] / [8.0.0]

### 🚀 Added
- ✨ Support for **Laravel 9 and 8** (PHP 7.3 or higher).

## [1.0.2]

### 🔧 Changed
- 📦 Updated composer to use the master Trello API.

## [1.0.1]

### 🐛 Fixed
- 🩹 Minor wrapper improvements.

## [1.0.0]

### 🎉 Added
- 🚀 Initial public release of the Laravel Trello API wrapper.
