# Agent Instructions

## Project Overview
This repository contains `SimplePhpCache`, a small PHP 8.2+ file-based cache library
for HTML output and variable caching. It is actively used in production.
The codebase has legacy origins (PHP 5.2 era), but is maintained with a focus on
pragmatic modernization, strong backward compatibility, and clear documentation.
Goal: modern baseline with minimal overhead and Packagist-readiness.

## Source of Truth
Use these files first when relevant:
- README.md for runtime behavior, documented API, and migration notes
- composer.json for dependencies, scripts, and autoloading
- src/ for implementation (SimplePhpCache.php)
- tests/ for behavior checks (PHPUnit)
- CHANGELOG.md for release notes
- TODO.md for deferred or optional work
- SECURITY.md for vulnerability reporting policy
- docs/security-notes.md for known risks, hardening notes, and security findings
- .github/workflows for CI behavior

## Core Working Rules
- Keep the public API backward compatible whenever possible.
- If a change could be a breaking change, stop and ask before implementing.
- If requirements are unclear, ask a focused clarification question before changing behavior.
- Do not overengineer: this is a small project, prioritize essentials.
- Prefer small, reviewable commits.
- Write comments and docs in English.
- Use explicit visibility on new methods and properties.
- Apply a security-first mindset when touching cache keys, file paths, serialization, and user-controlled input.

## Project Structure and Conventions
- Source: `src/SimplePhpCache.php` (PSR-4 autoloading, namespace `Tschueller\SimplePhpCache`)
- Tests: `tests/` (PHPUnit 11, PHP 8.2+)
- CI: `.github/workflows/ci.yml` (PHP 8.2 / 8.3 / 8.4)
- Static analysis: PHPStan level 3 (`phpstan.neon`)
- Quality scripts: `composer lint | test | analyse | check`

Do not add large refactorings or extensive test suites unless explicitly requested.
Deferred improvements are tracked in TODO.md.

## Versioning and Tagging Policy
- Preserve current state as an initial zero-series baseline tag so it can be referenced later.
- If no suitable tag exists, propose one (example: 0.1.0 or 0.0.0-legacy-baseline) with short rationale.
- Ask before creating the final tag if naming or semantics are ambiguous.
- Document the tagging outcome in the final report.

## Breaking Change Policy
- Breaking changes require prior approval.
- If approved, document clearly in README.md:
  - what changed
  - why it changed
  - migration steps

## Security Review Policy
For each relevant change, perform a lightweight security review:
- identify risks
- classify severity: critical, high, medium, low
- provide concrete mitigation
- call out anything requiring urgent action

## TODO Policy
Add items to TODO.md instead of implementing now when they are:
- optional enhancements
- larger refactorings
- non-critical expansions (for example broader test coverage)

Mark priority and short rationale for each TODO item.

## Quality Gates
Before finalizing:
- run syntax validation
- run static analysis
- run unit tests (basic suite)
- run any configured lint scripts if present

Use project scripts where available (for example via Composer).

## Definition of Done
Final response must include:
- summary of what changed
- changed and created files
- changelog status: updated or not needed (with reason)
- test impact: added/updated or not needed (with reason)
- validation commands and outcomes
- security findings and severity
- deferred items moved to TODO.md
