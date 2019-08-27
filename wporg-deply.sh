#!/bin/bash

# Note that this does not use pipefail
# because if the grep later doesn't match any deleted files,
# which is likely the majority case,
# it does not exit with a 0, and I only care about the final exit.
set -eo

# Ensure SVN username is set
# IMPORTANT: while secrets are encrypted and not viewable in the GitHub UI,
# they are by necessity provided as plaintext in the context of the Action,
# so do not echo or use debug mode unless you want your secrets exposed!
if [[ -z "$SVN_USERNAME" ]]; then
	echo "Set the SVN_USERNAME secret or environment variable"
	exit 1
fi

# Set the SLACK Announce URL
if [[ -z "$SLACKURL" ]]; then
	echo "Set the SLACKURL secret  or environment variable"
	exit 1
fi

# Git path is the current working dir
if [[ -z "$GITPATH" ]]; then
	GITPATH=`pwd`
fi
echo "ℹ︎ GITPATH is $GITPATH"

# Allow some ENV variables to be customized
if [[ -z "$SLUG" ]]; then
	SLUG="wc-vendors"
fi
echo "ℹ︎ SLUG is $SLUG"

# WordPress.org assets dir
if [[ -z "$ASSETS_DIR" ]]; then
	ASSETS_DIR=".wordpress-org"
fi
echo "ℹ︎ ASSETS_DIR is $ASSETS_DIR"

# Get version from the readme
if [[ -z "$VERSION" ]]; then
	VERSION=`grep "^Stable tag:" $GITPATH/readme.txt | awk -F' ' '{print $NF}' | tr -d '\015'`
fi
echo "ℹ︎ VERSION is $VERSION"

SVN_URL="https://plugins.svn.wordpress.org/${SLUG}/"
SVN_DIR="/tmp/${SLUG}"

# Checkout just trunk and assets for efficiency
# Tagging will be handled on the SVN level
echo "➤ Checking out .org repository..."
svn checkout --depth immediates "$SVN_URL" "$SVN_DIR"
cd "$SVN_DIR"
svn update --set-depth infinity assets
svn update --set-depth infinity trunk

echo "➤ Copying files..."
cd "$GITPATH"

# "Export" a cleaned copy to a temp directory
TMP_DIR="/tmp/${SLUG}-clean"
rm -rf "$TMP_DIR"
mkdir "$TMP_DIR"

# This will exclude everything in the .gitattributes file with the export-ignore flag
git archive HEAD | tar x --directory="$TMP_DIR"

cd "$SVN_DIR"

# Copy from clean copy to /trunk, excluding dotorg assets
# The --delete flag will delete anything in destination that no longer exists in source
rsync -rc "$TMP_DIR/" trunk/ --delete

# Copy dotorg assets to /assets
rsync -rc "$GITPATH/$ASSETS_DIR/" assets/ --delete

# Add everything and commit to SVN
# The force flag ensures we recurse into subdirectories even if they are already added
# Suppress stdout in favor of svn status later for readability
echo "➤ Preparing files..."
svn add . --force > /dev/null

# SVN delete all deleted files
# Also suppress stdout here
svn status | grep '^\!' | sed 's/! *//' | xargs -I% svn rm % > /dev/null

# Copy tag locally to make this a single commit
echo "➤ Copying tag..."
svn cp "trunk" "tags/$VERSION"

svn status

echo "➤ Committing files..."
svn commit -m "Update to version $VERSION from GitHub" --no-auth-cache --non-interactive  --username "$SVN_USERNAME"

echo "➤ Announcing to slack..."
curl -X POST -H 'Content-type: application/json' --data "{\"text\":\"✓ WC Vendors Marketplace $VERSION deployed to wordpress.org\"}" $SLACKURL

echo "✓ Plugin deployed!"
