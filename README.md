[contributors-shield]: https://img.shields.io/github/contributors/jobmetric/rolix.svg?style=for-the-badge
[contributors-url]: https://github.com/jobmetric/rolix/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/jobmetric/rolix.svg?style=for-the-badge&label=Fork
[forks-url]: https://github.com/jobmetric/rolix/network/members
[stars-shield]: https://img.shields.io/github/stars/jobmetric/rolix.svg?style=for-the-badge
[stars-url]: https://github.com/jobmetric/rolix/stargazers
[license-shield]: https://img.shields.io/github/license/jobmetric/rolix.svg?style=for-the-badge
[license-url]: https://github.com/jobmetric/rolix/blob/master/LICENCE.md
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-blue.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/majidmohammadian

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![MIT License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

# Rolix

**Roles, Memberships, and Permissions for Laravel. Built for Scale.**

Rolix is an advanced role and membership system for Laravel applications. Stop wiring ad-hoc permission checks across modules and start managing hierarchical roles, scoped memberships, file-based permissions, and runtime rule evaluators in one consistent package. It is designed for large-scale, multi-tenant, and modular applications—where access control must stay fast, auditable, and extensible.

## Why Rolix?

### Hierarchical Roles & Scoped Memberships

Define role trees per type, assign people to system-wide or memberable contexts, and evaluate permissions with allow/deny lists, wildcards, owner shortcuts, and super roles.

### File-Based Permission Catalog

Register permission definitions from PHP files by context (and optional model scope). Collect paths at boot through domain events so packages and plugins can contribute their own permission files.

### Gate, Middleware & Blade Integration

Use Laravel Gate, the `rolix.permission` middleware, and `@rolixCan` Blade directive without writing controllers—authorization stays close to your personable models via `HasRole` and `HasMembers`.

### Rule Evaluators & Activity Log

Attach focused runtime rules (time, weekday, IP, and application environment) to roles, and keep an audit trail of role/membership mutations through the activity logger.

## What is Rolix?

Rolix models access as **roles**, **memberships**, and **permissions**:

- **Role** — named capability set (`allow` / `deny`) with optional hierarchy and rules
- **Membership** — links a personable model to a role in a system or memberable context
- **Permission** — string keys loaded from files and evaluated with wildcards

Use `HasRole` on users (or any personable model) and `HasMembers` on tenants/organizations to assign, sync, and check access. Permission snapshots are memoized per request and optionally stored in Laravel Cache with versioned invalidation.

## What Awaits You?

By adopting Rolix, you will:

- **Centralize access control** — one package for roles, memberships, and permissions
- **Scale multi-tenant apps** — scoped memberships, collections, and owner shortcuts
- **Integrate cleanly** — Gate, middleware, Blade, DomainEvent, and EventRegistry
- **Stay auditable** — activity logs for role and membership lifecycle events
- **Extend safely** — RoleTypeRegistry, RuleEvaluatorRegistry, and permission path events

## Quick Start

Install Rolix via Composer:

```bash
composer require jobmetric/rolix
```

## Documentation

Ready to build solid access control? Our documentation is the source of truth for Rolix:

**[📚 Read Full Documentation →](https://jobmetric.github.io/packages/rolix/)**

The documentation includes:

- **Getting Started** — overview and mental model
- **Installation** — composer, config, migrations, and cache settings
- **Showcase** — multi-tenant RBAC walkthrough
- **HasRole & HasMembers** — traits for personable and memberable models
- **Services** — Role, Membership, and PermissionManager APIs
- **Gate / Middleware / Blade** — authorization surfaces
- **Events & Activity Log** — DomainEvent keys and audit trail
- **Rule Evaluators** — built-in drivers and custom evaluators

## Contributing

Thank you for participating in `rolix`. A contribution guide can be found [here](CONTRIBUTING.md).

## License

The `rolix` package is open-sourced software licensed under the MIT license. See [License File](LICENCE.md) for more information.
