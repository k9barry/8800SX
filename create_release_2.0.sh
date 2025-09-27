#!/bin/bash
# Script to create Release 2.0 for 8800SX
# Run this script to create the release with proper tag and notes

set -e

REPO="k9barry/8800SX"
TAG="2.0"
TARGET_COMMIT="7b41ae1f4eb63c001fd8aaaf5b71f792576ff97a"
RELEASE_TITLE="Release 2.0 - Major Security Enhancements & Infrastructure Modernization"
RELEASE_NOTES_FILE="RELEASE_NOTES_2.0.md"

echo "Creating Release 2.0 for $REPO..."
echo "Target commit: $TARGET_COMMIT"
echo "Tag: $TAG"
echo ""

# Check if gh CLI is available
if command -v gh &> /dev/null; then
    echo "Using GitHub CLI to create release..."
    
    # Create the release using GitHub CLI
    gh release create "$TAG" \
        --repo "$REPO" \
        --title "$RELEASE_TITLE" \
        --notes-file "$RELEASE_NOTES_FILE" \
        --target "$TARGET_COMMIT"
        
    echo "✅ Release created successfully using GitHub CLI!"
    echo "View at: https://github.com/$REPO/releases/tag/$TAG"
    
elif command -v git &> /dev/null; then
    echo "GitHub CLI not found. Creating tag with git..."
    echo "You'll need to create the release manually via GitHub web interface."
    echo ""
    
    # Create and push the tag
    git tag -a "$TAG" -m "Release 2.0: Major security enhancements and infrastructure modernization" "$TARGET_COMMIT"
    git push origin "$TAG"
    
    echo "✅ Tag created and pushed successfully!"
    echo ""
    echo "📝 Next steps:"
    echo "1. Go to: https://github.com/$REPO/releases/new?tag=$TAG"
    echo "2. Set title: $RELEASE_TITLE"
    echo "3. Copy content from: $RELEASE_NOTES_FILE"
    echo "4. Ensure 'Set as pre-release' is unchecked"
    echo "5. Click 'Publish release'"
    
else
    echo "❌ Error: Neither 'gh' nor 'git' command found."
    echo "Please install GitHub CLI (gh) or Git to proceed."
    exit 1
fi

echo ""
echo "Release information:"
echo "- Repository: $REPO"
echo "- Tag: $TAG"
echo "- Target: $TARGET_COMMIT"
echo "- Title: $RELEASE_TITLE"
echo "- Notes: $RELEASE_NOTES_FILE"