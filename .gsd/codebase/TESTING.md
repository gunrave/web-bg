# Testing Patterns

> Analyzed from: Laravel 11 + Filament 3 project (web-bg)

---

## Testing Frameworks

| Framework | Version | Role |
|-----------|---------|------|
| **Pest** | ^2.34 | Primary test runner and assertion DSL |
| **pestphp/pest-plugin-laravel** | ^2.4 | Laravel helpers (actingAs, artisan, etc.) |
| **PHPUnit** | (peer dep of Pest) | Underlying test engine |
| **Mockery** | ^1.6 | Mocking/stubbing library |

Tests are written in **Pest v2** syntax (closure-based), not raw PHPUnit class-based style.

---

## Test Directory Structure

tests/
  Pest.php          - Global Pest configuration (uses(), expect extensions, helpers)
  TestCase.php      - Base TestCase extending Laravel foundation TestCase
  Feature/
    ExampleTest.php - Scaffold example (HTTP response assertion)
  Unit/
    ExampleTest.php - Scaffold example (basic truthiness assertion)

File: tests/Feature/ExampleTest.php
File: tests/Unit/ExampleTest.php

Only scaffold/example tests exist. No domain-specific tests have been written yet.

---

## Test File Naming

- Files named PascalCase + Test suffix: e.g., ExampleTest.php
- Placed in tests/Feature/ or tests/Unit/ matching PHPUnit suite config
- phpunit.xml maps:
  - tests/Unit to Unit suite
  - tests/Feature to Feature suite

---

## Types of Tests Present

### Feature Tests (tests/Feature/)
HTTP-level integration tests using Laravel test client.
File: tests/Feature/ExampleTest.php uses Pest it() function.
Feature tests have access to this->get(), this->post(), etc. via the Laravel plugin.

### Unit Tests (tests/Unit/)
Pure logic tests with no framework dependencies.
File: tests/Unit/ExampleTest.php uses Pest test() function with expect() API.

### Integration / E2E / Browser Tests
- No integration tests beyond HTTP feature tests
- No Dusk (browser) tests
- No API endpoint tests
- No Filament panel-specific tests

---

## Pest Configuration

File: tests/Pest.php

Key observations:
- RefreshDatabase is commented out - Feature tests do not reset the database between runs by default
- The uses() binding applies only to Feature/ directory
- Unit tests run with bare PHPUnit TestCase (no Laravel integration)
- A custom toBeOne expectation is registered via expect()->extend()
- A stub global helper something() is defined (scaffold placeholder, unused)

File: tests/TestCase.php - empty extension of Laravel base test case. No custom setup, teardown, or shared traits added.

---

## Mocking / Stubbing Approach

- Mockery (mockery/mockery ^1.6) is installed and available
- No mocks found in existing tests (only scaffold tests exist)
- Laravel built-in this->mock() and this->spy() available via pest-plugin-laravel
- No custom mock helpers or fakes defined

---

## Test Data Setup

### Factories
Only UserFactory exists at database/factories/UserFactory.php:
- Uses fake() helper (Faker)
- Defines definition() with: name, email, email_verified_at, password, remember_token
- Has unverified() state method that nulls email_verified_at

No factories exist for domain models: Pegawai, Gaji, Tagihan, Tunker, Potong, PeriodeTagihan.

### Seeders
File: database/seeders/DatabaseSeeder.php
- Seeds one test user via UserFactory::create()
- No domain data seeders

### In-Memory SQLite
phpunit.xml has SQLite in-memory config commented out.
Tests run against whatever database is configured in .env or .env.testing.

---

## Environment Configuration (phpunit.xml)

- APP_ENV = testing
- BCRYPT_ROUNDS = 4 (faster hashing in tests)
- CACHE_STORE = array (in-memory cache)
- MAIL_MAILER = array (no real mail sent)
- QUEUE_CONNECTION = sync (jobs run synchronously)
- SESSION_DRIVER = array (in-memory sessions)
- PULSE_ENABLED = false
- TELESCOPE_ENABLED = false

---

## Coverage Configuration

phpunit.xml source block covers app/ directory.
- No minimum coverage threshold configured
- No coverage enforcement (no CI pipeline found)
- Coverage driver not specified (requires Xdebug or PCOV)

---

## How to Run Tests

Run all tests:
  php artisan test
  ./vendor/bin/pest

Run specific suite:
  php artisan test --testsuite=Feature
  php artisan test --testsuite=Unit

Run specific file:
  ./vendor/bin/pest tests/Feature/ExampleTest.php

Run with coverage:
  ./vendor/bin/pest --coverage

Run in parallel:
  ./vendor/bin/pest --parallel

---

## Current Test Coverage Assessment

| Area | Tests Written |
|------|--------------|
| Models | None |
| Filament Resources | None |
| Import pipelines | None |
| HTTP routes | Scaffold only (GET /) |
| Authentication | None |
| Database relationships | None |

The project is at the scaffold stage of testing. The testing infrastructure (Pest v2, Mockery, factory support) is fully configured, but no domain tests have been authored.
