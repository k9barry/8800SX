# GitHub Actions Workflows

This repository uses several GitHub Actions workflows to maintain code quality and automate releases.

## Workflows

### 1. Code Formatter (`code-formatter.yml`)

Automatically formats PHP code to comply with PSR-12 coding standards.

**Triggers:**
- **Manual**: Can be triggered manually from the Actions tab
  - Go to Actions → Code Formatter → Run workflow
  - Optionally specify a branch to format
- **Automatic**: Runs on pull requests (opened or synchronized)

**What it does:**
- Formats all PHP files in `data/web/app/` using php-cs-fixer
- Applies PSR-12 coding standards plus additional rules:
  - Short array syntax (`[]` instead of `array()`)
  - Alphabetically ordered imports
  - Removes unused imports
  - Consistent operator spacing
  - Proper PHPDoc formatting
- Automatically commits formatted code back to the branch
- Comments on PRs when formatting is applied

**Usage:**

To manually format code:
1. Go to the [Actions tab](../../actions)
2. Select "Code Formatter" workflow
3. Click "Run workflow"
4. Select the branch you want to format (or leave empty for current branch)
5. Click "Run workflow"

The workflow will automatically commit any formatting changes back to your branch.

**Note:** The commit message includes `[skip ci]` to prevent triggering other workflows.

### 2. Code Quality (`code-quality.yml`)

Checks code quality without making changes.

**Triggers:**
- Push to `main` branch
- Pull requests to `main` branch

**What it checks:**
- **PHP Syntax**: Validates all PHP files for syntax errors
- **PHP Code Style**: Checks PSR-12 compliance (dry-run, doesn't fix)
- **YAML Validation**: Validates all YAML files
- **Markdown Linting**: Checks markdown formatting
- **Dockerfile Linting**: Validates Dockerfile using hadolint

### 3. Semantic Versioning (`semantic-versioning.yml`)

Automatically updates version numbers and creates releases.

**Triggers:**
- When a pull request is merged to `main`

**What it does:**
- Reads current version from `version.txt`
- Determines version bump type from PR labels:
  - `major`: Breaking changes (e.g., 1.0.0 → 2.0.0)
  - `minor`: New features (e.g., 1.0.0 → 1.1.0)
  - `patch`: Bug fixes (e.g., 1.0.0 → 1.0.1, default)
- Updates `version.txt`
- Creates a GitHub release with the new version tag

**Usage:**
Add one of these labels to your PR before merging:
- `major` - for breaking changes
- `minor` - for new features
- `patch` - for bug fixes (default if no label)

## Best Practices

### Before Committing Code

Run the code formatter locally if you have php-cs-fixer installed:
```bash
php-cs-fixer fix --config=.php-cs-fixer.php data/web/app/
```

Or push to a PR and let the workflow format it automatically.

### Code Quality Checks

All PRs should pass code quality checks before merging. If the checks fail:
1. Review the workflow logs to see what failed
2. Fix the issues locally
3. Push the fixes to your PR branch

### Versioning

When creating a PR:
1. Add an appropriate version label (`major`, `minor`, or `patch`)
2. Update `CHANGELOG.md` with your changes
3. When merged, the version will be automatically bumped

## Troubleshooting

### Code Formatter Issues

If the formatter fails:
1. Check the workflow logs in the Actions tab
2. Ensure your PHP code is syntactically valid
3. The workflow skips `tcpdf` directory and certain file patterns

### Permission Issues

If workflows fail with permission errors:
- Check that the workflow has appropriate permissions in the YAML file
- Ensure `GITHUB_TOKEN` has necessary scopes

### Workflow Not Triggering

If a workflow doesn't trigger:
1. Check the trigger conditions in the workflow file
2. Verify you're pushing to the correct branch
3. Check if `[skip ci]` is in the commit message (which prevents workflows)

## Additional Resources

- [PHP CS Fixer Documentation](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)
- [PSR-12 Coding Style Guide](https://www.php-fig.org/psr/psr-12/)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Semantic Versioning](https://semver.org/)
