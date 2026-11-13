# Debtlist

Lightweight loan and expense list built on [VC Framework](https://github.com/dsoos1290/vcframework) for PHP 5.3 and newer.

Debtlist keeps loans, bills and subscriptions in one per-user list, with configurable billing periods, multi-currency estimates and grouped totals.

## Features

* Per-user loan and recurring expense lists with add, edit and delete actions.
* **B**, **P** and **W** groups, with each entry assigned to one group.
* Monthly loans with an optional insurance fee, first payment date, installment count and calculated final payment date.
* Weekly, monthly, quarterly, half-yearly and yearly expenses, with optional end dates.
* Minimum, base and maximum amounts for each entry; fixed amounts need only a base value.
* HUF, EUR, USD and other three-letter currency codes, with manually maintained per-user exchange-rate ranges.
* Configurable billing periods, starting on the 15th by default and ending on the 14th of the following month.
* Per-group and overall minimum, base and maximum totals for the selected period and monthly average.
* Twelve-period forecast, actual weekly occurrence counts and automatic handling of expired loans.
* Shared user accounts with sibling applications, compatible PHP sessions, CSRF protection and flash messages.
* Responsive desktop and mobile interface.
* MySQLi, optional database table prefixes, Apache URL rewriting and subdirectory support.
* No Composer dependency; PHP 5.3+ compatible.

## Requirements

* PHP 5.3+ with MySQLi.
* A secure random source: native PHP random bytes, OpenSSL or readable `/dev/urandom`.
* MySQL or MariaDB with utf8mb4 support.
* Apache with `mod_rewrite` and `.htaccess` overrides enabled.
* Writable `private_html/log` directory for error logging.

## Installation

1. Download the [latest release](https://github.com/sdaniel1335/debtlist/releases/latest) or clone the repository.
2. Rename `private_html/app/config/db-sample.php` to `private_html/app/config/db.php` and configure the database. Use the same database and table prefix as the sibling applications when sharing accounts.
3. Import `install.sql` into the configured database. If using `DB_PREFIX`, apply that prefix to all table names in the SQL before importing.
4. Open the application and sign in with an existing shared account, or `admin` / `admin` on a fresh installation.
5. **Immediately change the default password** through the shared account management before deployment.
6. Add exchange rates for any foreign currencies, then create your loans and recurring expenses.

The installer preserves existing users and inserts the default account only if the username does not already exist. Debtlist does not include a password-change page; manage passwords through a sibling application using the same `users` table.

**Security:** Keep `private_html` inaccessible to direct HTTP requests and set `APP_ENV` to `prod` for deployment.

## Configuration

* Application settings: `private_html/app/config/app.php` (`APP_URL` is optional).
* Database settings: `private_html/app/config/db.php` (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_CHAR`, `DB_TIME`, `DB_PREFIX`). Use `utf8mb4` for `DB_CHAR`.
* Period settings: `DEBT_PERIOD_DAY` in `private_html/app/config/debtlist.php`, from 1 to 28; default: **15**.
* Exchange rates: configure minimum, base and maximum HUF values through the authenticated **Árfolyamok** page. HUF is fixed at 1.
* The interface is currently in Hungarian.

Shared sign-in requires matching PHP session names, cookie paths and domains, and session storage across applications on the same host. Debtlist uses `PHPSESSID`, the `/` cookie path and the shared `auth_user_id` and `auth_username` session fields. Adjust `private_html/app/config/debtlist.php` to match the other applications when needed. Signing out also clears the shared login state.

## Periods and estimates

An entry due on the period's starting day belongs to that period; the next period's starting day is excluded. Weekly expenses are counted every seven calendar days from their first due date. Monthly and less frequent expenses retain the original day where possible, falling back to the last day of shorter months.

The monthly average is the total for the twelve periods beginning with the selected period, divided by twelve. It includes entries that start or expire within that range. Loan insurance is added to every installment in the loan's currency.

Minimum estimates use the minimum amount and exchange rate; maximum estimates use their maximum values. Missing exchange rates are flagged instead of silently treated as zero. Totals are displayed in HUF, rounded only for display.

Debtlist is a planning list: it does not track completed payments, principal balances or historical amount changes. Editing an amount or exchange rate updates estimates for every viewed period.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the release history.
