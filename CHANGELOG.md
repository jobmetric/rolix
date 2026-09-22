# Changelog

All notable changes to `jobmetric/rolix` are documented here.

## 1.2.1 - 2026-09-22

- Remove the retired location, role-count, quota, and custom-expression evaluator classes and translations.
- Cover array-based environment selection and the simplified user-status rule.

## 1.2.0 - 2026-09-22

- Focus the built-in evaluator registry on time, weekday, user status, IP range, and environment rules.
- Replace free-form timezone, user-status, and environment settings with translated selections.
- Expand IP range and environment guidance with actionable examples.

## 1.1.3 - 2026-09-22

- Add localized usage descriptions for every built-in role-rule evaluator.

## 1.1.2 - 2026-09-22

- Add localized display names for every built-in role-rule evaluator.

## 1.1.1 - 2026-09-22

- Fix flattening permission and language lists from named contexts on PHP 8.

## 1.1.0 - 2026-09-22

- Add hierarchical role trees with closure-table path maintenance.
- Add typed role registries and system-scoped roles.
- Add file-based permission catalogs and nested permission trees.
- Add allow/deny permission evaluation, caching, Gate, middleware, and Blade integration.
- Add configurable rule evaluators with form metadata and validated role-rule persistence.
- Add role and membership activity logging and DomainEvent integration.
- Add Laravel 13 compatibility coverage through the supported dependency ranges.
