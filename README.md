## Supabase (self-hosted) setup

Dieses Projekt nutzt self-hosted Supabase (siehe `docker-compose.yml` im Repo-Root) als
PostgreSQL-Datenbank und Identity-Provider. Details zur geplanten Auth-Architektur stehen in
`ARCHITECTURE.md`. Es wird bewusst **keine** Supabase-Cloud-Instanz verwendet.

1. Den Stack aus `docker-compose.yml` deployen (z. B. via Coolify). Coolify generiert dabei die
   `SERVICE_PASSWORD_*`/`SERVICE_URL_*`-Werte automatisch (siehe `.env.supabase.example` für die
   vollständige, dokumentierte Liste aller vom Compose-File genutzten Variablen inkl. der
   erreichbaren Host/Port-Kombination für den `supabase-supavisor`-Service/Transaction Pooler).
   Die echte, befüllte Variante gehört in `.env.supabase` (gitignored), niemals in
   `.env.supabase.example`.
2. `.env` aus `.env.example` erstellen und `DB_URL` mit dem echten Pooler-Connection-String
   sowie den generierten Zugangsdaten befüllen. `DB_SCHEMA` legt fest, in welchem
   PostgreSQL-Schema (Standard: `laravel`) Laravel arbeitet, statt `public` zu benutzen
   (siehe `config/database.php` → `search_path`).
3. **Einmalig, vor dem ersten `migrate`**, das Ziel-Schema in der Datenbank anlegen (Postgres
   legt Schemas nicht automatisch an) — z. B. über den SQL-Editor im Supabase Studio des
   self-hosted Stacks, oder per `psql`:
   ```sql
   CREATE SCHEMA IF NOT EXISTS "laravel";
   ```
4. `php artisan migrate` ausführen.
5. `php artisan serve` starten und `http://127.0.0.1:8000` öffnen.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
