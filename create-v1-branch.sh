#!/bin/bash
#
# Script to create v1.0.0 branch from commit bc9bb59008f514bdb6059b4cd04d84bcbe1bfe9b
#
# Usage: ./create-v1-branch.sh
#

set -e

COMMIT_SHA="bc9bb59008f514bdb6059b4cd04d84bcbe1bfe9b"
BRANCH_NAME="v1.0.0"

echo "Creating branch $BRANCH_NAME from commit $COMMIT_SHA..."

# Ensure we have the full history
if [ -f .git/shallow ]; then
    echo "Unshallowing repository..."
    git fetch --unshallow --all --tags
fi

# Verify commit exists
if ! git cat-file -t "$COMMIT_SHA" &> /dev/null; then
    echo "Error: Commit $COMMIT_SHA not found"
    exit 1
fi

# Check if branch already exists locally
if git show-ref --verify --quiet "refs/heads/$BRANCH_NAME"; then
    echo "Branch $BRANCH_NAME already exists locally"
else
    echo "Creating local branch $BRANCH_NAME..."
    git branch "$BRANCH_NAME" "$COMMIT_SHA"
fi

# Check if branch exists on remote
if git ls-remote --heads origin "$BRANCH_NAME" | grep -q "$BRANCH_NAME"; then
    echo "Branch $BRANCH_NAME already exists on remote"
else
    echo "Pushing branch $BRANCH_NAME to origin..."
    git push origin "$BRANCH_NAME"
fi

echo "✅ Branch $BRANCH_NAME created successfully!"
echo ""
echo "Verification:"
git log --oneline "$BRANCH_NAME" -1
