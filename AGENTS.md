# Agent Instructions

## Project Overview
This repository contains `SimplePhpCache`, a modern PHP 8.2+ Composer library
for file-based HTML-output and variable caching. It is actively used in
production and has an established 1.x release history on Packagist.

The public API is PSR-4 namespaced and typed. Variable values are stored as
JSON payloads, cache files are atomically published, and optional safe cache
namespaces support several applications or tenants below one cache base
directory. Maintain the stable 1.x API with minimal overhead and clear
migration guidance.

## Source of Truth
Use these files first when relevant:
- README.md for runtime behavior, documented API, and migration notes
- composer.json for dependencies, scripts, and autoloading
- src/ for implementation (SimplePhpCache.php)
- tests/ for behavior checks (PHPUnit)
- CHANGELOG.md for release notes
- docs/releasing.md for the release procedure and release-workflow checks
- docs/migration.md for supported migration paths and the PhaseUtils fork
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
- Apply a security-first mindset when touching cache keys, file paths, JSON
  payloads, cache namespaces, and user-controlled input.
- Keep the 1.x cache format and public API compatible. Changes to the JSON
  payload format, cache-file naming, cache-directory layout, or namespace
  validation require explicit migration and release consideration.

## Project Structure and Conventions
- Source: `src/SimplePhpCache.php` (PSR-4 autoloading, namespace `Tschueller\SimplePhpCache`; typed static API)
- Tests: `tests/` (PHPUnit 11, PHP 8.2+; variable and HTML caching, TTL,
  cache clearing/counting, namespaces, JSON migration, and atomic writes)
- CI and release automation: `.github/workflows/` (PHP 8.2 / 8.3 / 8.4)
- Static analysis: PHPStan level 8 (`phpstan.neon`)
- Quality scripts: `composer lint | test | analyse | check`

Do not add large refactorings or extensive test suites unless explicitly requested.
Deferred improvements are tracked in TODO.md.

The v1.x cache directory is `<cacheBaseDir>/.simplePhpCache`, optionally with
one validated `$cacheNamespace` subdirectory. An explicit `$cacheBaseDir` is
required for production; the temporary-directory fallback is deprecated and
scheduled for removal in v2.0.

## Versioning and Tagging Policy
- The package has an established 1.x release history; derive the next version
  and release notes from the current tags, `CHANGELOG.md`, and release workflow.
- Before a release, verify the current branch, working tree, tags, remote publication state, and Packagist version instead of relying on historical release assumptions.
- For a release tag, the workflow validates Composer metadata, linting, tests,
  PHPStan, the versioned and dated changelog entry, and an empty
  `[Unreleased]` section before creating the GitHub release.
- Ask before creating the final tag if naming or semantics are ambiguous.
- Document the tagging outcome in the final report.

After a substantial refactoring, review this `AGENTS.md` before the release and update it when project structure, tooling, quality gates, release automation, or other repository guidance has changed.

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

For variable-cache work, do not reintroduce PHP deserialization or object
caching. For filesystem work, retain atomic publication and validate any value
that can influence a cache path.

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
