# Releasing

This project follows a simple and automated release workflow based on Git tags
and GitHub Actions.

## Release Process

### 1. Make changes

Implement changes using feature branches and merge them into `master` via pull
requests.

### 2. Update the changelog

All user-facing changes must be documented in `CHANGELOG.md` under the
`[Unreleased]` section:

```
## [Unreleased]

### Added
- New feature

### Fixed
- Bug fix
```

### 3. Prepare the release

Before creating a release, move all entries from `[Unreleased]` into a new
version section:

```
## [Unreleased]

## [1.2.0] - 2026-04-06

### Added
- New feature

### Fixed
- Bug fix
```

Make sure:

* The version matches the upcoming release tag without its `v` prefix.
* The date is in `YYYY-MM-DD` format.
* The `[Unreleased]` section is empty afterward.

### 4. Create a Git tag

Commit the changelog changes:

```
git add CHANGELOG.md
git commit -m "Prepare release v1.2.0"
git push origin master
```

Create and push an annotated version tag:

```
git tag -a v1.2.0 -m "Release v1.2.0"
git push origin v1.2.0
```

### 5. Automated release

Pushing a `v*` tag triggers the GitHub Actions release workflow. It will:

* validate `composer.json`;
* run syntax checks, unit tests, and static analysis;
* validate the changelog;
* extract the correct changelog section;
* create a GitHub release with release notes.

## Validation

The release workflow will fail if:

* Composer metadata is invalid;
* linting, tests, or static analysis fail;
* no changelog entry exists for the version;
* the `[Unreleased]` section is not empty;
* the version entry is missing a valid date.

## Troubleshooting

### Tag points to the wrong commit

If GitHub Actions fails or a release tag was pushed before the final
workflow/changelog fixes were merged, delete and recreate the tag on the
correct commit:

```bash
# Delete local tag
git tag -d v1.2.0

# Delete remote tag
git push origin :refs/tags/v1.2.0

# Recreate tag on current HEAD (or replace HEAD with an explicit commit SHA) and push it again
git tag -a v1.2.0 -m "Release v1.2.0"
git push origin v1.2.0
```

Then verify that a new GitHub Actions run starts for the recreated tag.

If a release is already publicly consumed, prefer creating a new patch version
(for example `v1.2.1`) instead of moving an existing release tag.

## Versioning

This project follows Semantic Versioning:

* `PATCH` - bug fixes;
* `MINOR` - backward-compatible features;
* `MAJOR` - breaking changes.
