# Changelog

All notable changes to this project will be documented in this file.

## [0.1.0] - 2026-11-13

### Added

- Initial Debtlist application built on [VC Framework](https://github.com/dsoos1290/vcframework) with PHP 5.3+ compatibility.
- MySQLi-backed per-user loan and recurring expense lists with add, edit and delete operations.
- B, P and W groups with per-group and overall totals.
- Monthly loans with optional insurance, installment counts and calculated final payment dates.
- Weekly, monthly, quarterly, half-yearly and yearly recurring expenses with optional end dates.
- Configurable billing periods, starting on the 15th by default.
- Minimum, base and maximum amounts, multi-currency entries and per-user manual exchange-rate ranges.
- Selected-period totals, twelve-period monthly averages and a twelve-period forecast.
- Calendar-aware occurrence counting, month-end date adjustment and loan expiry handling.
- Shared user accounts and compatible PHP sessions, CSRF protection and flash messages.
- Responsive desktop and mobile layouts.
- Optional database table prefixes, absolute application URL and subdirectory installation support.
- Apache URL rewriting and no Composer dependency.
