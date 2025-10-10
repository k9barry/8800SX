# Repository Rollback to bc9bb59 - INSTRUCTIONS

## Current Status

This branch contains the repository state from commit `bc9bb59` (June 17, 2025).

## What Changed

The repository has been rolled back from v3.0.1 to commit bc9bb59, removing:
- 129 commits
- Multi-container architecture (v3.0+)
- Traefik integration  
- Advanced documentation (REPOSITORY_STRUCTURE.md, SECURITY.md, CHANGELOG.md)
- Custom database Dockerfile
- Semantic versioning workflows

## Repository State Now

✅ Simple Docker Compose with nginx + php-fpm  
✅ Basic file structure from June 2025
✅ data/web/index.php with redirect to alignments page
✅ secrets/db_password.txt authentication
✅ Basic README

## Important: How to Apply This Rollback

**Option 1: Force Push (if you have admin access)**
```bash
# From the main branch:
git reset --hard bc9bb59
git push --force origin main
```

**Option 2: Merge This PR**

Because this is a rollback that removes 129 commits, standard merging won't work cleanly. When merging this PR:

1. Be aware this is destructive and will lose recent work
2. You may need to use `--allow-unrelated-histories` or force merge
3. Alternative: cherry-pick specific files you want to restore instead of full rollback

## Why This Approach?

The automated PR system cannot force-push, so this branch shows the file state at bc9bb59. To apply the rollback to main, manual intervention is required using one of the options above.

## Questions?

If you only wanted to restore specific files rather than a full rollback, please clarify and we can provide a more targeted solution.
