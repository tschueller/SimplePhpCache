# Contributing

Thank you for your interest in contributing!

This document describes the development workflow and guidelines for
contributing to this project.

---

## Development Workflow

### 1. Create a branch

All changes should be made in a separate branch based on `master`.

Branch naming convention:

* `feature/...` for new features;
* `bugfix/...` for bug fixes;
* `chore/...` for maintenance tasks.

Example:

```
git checkout -b feature/add-cache-inspection
```

---

### 2. Make your changes

* Keep changes focused, minimal, and backward compatible when possible.
* Follow the existing code style and conventions.
* Add or update tests if necessary.

---

### 3. Run checks locally

Before submitting your changes, make sure everything passes:

```
composer install
composer run check
```

---

### 4. Update the changelog

All user-facing changes must be added to `CHANGELOG.md` under the
`[Unreleased]` section.

Example:

```
## [Unreleased]

### Added
- Cache inspection support
```

---

### 5. Commit Message Guidance

Use clear, imperative messages. Prefer a simple Conventional Commit style such
as `feat:`, `fix:`, `docs:`, `refactor:`, `test:`, or `chore:`.

Examples:

* `fix: handle empty cache files safely`
* `feat: add configurable cache directory`
* `docs: add cache directory guidance`
* `refactor: simplify cache file handling`
* `test: add coverage for expired entries`
* `chore: update PHPStan configuration`

---

### 6. Open a Pull Request

Open a pull request against the `master` branch.

Please ensure:

* The CI pipeline passes.
* The changelog is updated.
* The pull request has a clear description.

---

### 7. Merge

Pull requests are typically merged using **Squash & Merge** to keep the
history clean.

---

## Coding Standards

This project uses:

* PHP syntax validation;
* PHPStan for static analysis;
* PHPUnit for testing.

Run all configured checks with:

```
composer run check
```

---

## Versioning & Releases

Releases are handled via Git tags and GitHub Actions.

Please do not create tags manually unless you are the maintainer.

For details, see [releasing.md](docs/releasing.md).

---

## Reporting Issues

Please include:

* steps to reproduce;
* expected behavior;
* actual behavior;
* PHP version and environment details.

---

## Questions

If you have any questions, feel free to open an issue or discussion.
