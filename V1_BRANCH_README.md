# Creating v1.0.0 Branch

## Overview
This document explains how to create the `v1.0.0` branch from commit `bc9bb59008f514bdb6059b4cd04d84bcbe1bfe9b`.

## Background
The v1.0.0 branch represents an early version of the repository that includes the initial index.php redirect functionality.

## Commit Information
- **Commit SHA**: `bc9bb59008f514bdb6059b4cd04d84bcbe1bfe9b`
- **Commit Message**: "Created index.php to redirect and changed target to _self"
- **Author**: K9 Barry <k9barry@gmail.com>
- **Date**: Tue Jun 17 12:26:56 2025 -0400

## Methods to Create the Branch

### Method 1: Using GitHub Actions Workflow (Recommended)
The easiest way to create the branch is using the provided GitHub Actions workflow:

1. Go to the repository on GitHub
2. Navigate to **Actions** tab
3. Select **"Create v1.0.0 Branch"** workflow
4. Click **"Run workflow"**
5. Select the branch to run from (usually `main`)
6. Click **"Run workflow"** button

The workflow will:
- Fetch the full repository history
- Verify the commit exists
- Create the v1.0.0 branch from the commit
- Push it to origin
- Provide a summary of the action

### Method 2: Using the Shell Script
If you have local access and push permissions:

```bash
# Run the script
./create-v1-branch.sh
```

The script will:
- Check if the repository needs unshallowing
- Verify the commit exists
- Create the branch locally
- Push it to origin
- Display verification information

### Method 3: Manual Git Commands
If you prefer manual control:

```bash
# Ensure you have the full history
git fetch --unshallow --all --tags

# Verify commit exists
git cat-file -t bc9bb59008f514bdb6059b4cd04d84bcbe1bfe9b

# Create the branch
git branch v1.0.0 bc9bb59008f514bdb6059b4cd04d84bcbe1bfe9b

# Push to origin
git push origin v1.0.0

# Verify
git log --oneline v1.0.0 -1
```

### Method 4: Using GitHub CLI
If you have `gh` CLI installed and authenticated:

```bash
# Create branch via API
gh api repos/k9barry/8800SX/git/refs \
  -f ref=refs/heads/v1.0.0 \
  -f sha=bc9bb59008f514bdb6059b4cd04d84bcbe1bfe9b

# Verify
gh api repos/k9barry/8800SX/branches/v1.0.0
```

### Method 5: Using GitHub Web Interface
1. Go to https://github.com/k9barry/8800SX
2. Click on the branch dropdown (usually shows "main")
3. Type `v1.0.0` in the search box
4. If the branch doesn't exist, you'll see "Create branch: v1.0.0"
5. Note: This will create from current HEAD, not from the specific commit
6. You'll need to use git commands or API to point it to the correct commit

## Verification
After creating the branch, verify it points to the correct commit:

```bash
# Fetch the latest branches
git fetch origin

# Check the branch
git log --oneline origin/v1.0.0 -1
```

Expected output:
```
bc9bb59 Created index.php to redirect and changed target to _self
```

Or via GitHub web interface:
1. Go to https://github.com/k9barry/8800SX/tree/v1.0.0
2. Check the latest commit SHA matches `bc9bb59`

## Troubleshooting

### Branch Already Exists
If the branch already exists:
```bash
# Check what commit it points to
git log --oneline origin/v1.0.0 -1

# If it's wrong, you can force update (be careful!)
git push origin bc9bb59008f514bdb6059b4cd04d84bcbe1bfe9b:refs/heads/v1.0.0 -f
```

### Commit Not Found
If the commit is not found:
```bash
# Unshallow the repository
git fetch --unshallow --all --tags

# Try again
git cat-file -t bc9bb59008f514bdb6059b4cd04d84bcbe1bfe9b
```

### Permission Denied
If you get permission errors:
- Ensure you have push access to the repository
- Check your GitHub token has `contents: write` permission
- Try authenticating with `gh auth login` if using GitHub CLI

## Why This Commit?
Commit `bc9bb59` represents a significant point in the repository history where:
- index.php was created for redirection functionality
- Target attribute was changed to `_self`
- The basic web application structure was established

This version can serve as a reference point for v1.0.0 of the application.
