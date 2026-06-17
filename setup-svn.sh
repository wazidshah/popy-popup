#!/usr/bin/env bash
#
# setup-svn.sh — One-time setup + repeatable sync for the Popy WordPress.org SVN repo.
#
# This keeps your Git repo (source of truth, dev history) completely separate
# from the SVN checkout (what WordPress.org actually serves to users).
#
# Usage:
#   ./setup-svn.sh setup        First-time checkout + apply svn:ignore
#   ./setup-svn.sh sync         Copy current plugin files into trunk/ (safe, excludes .git etc.)
#   ./setup-svn.sh release 1.0.0   Tag a release after syncing
#
set -euo pipefail

PLUGIN_SLUG="popy-popup"
SVN_URL="https://plugins.svn.wordpress.org/${PLUGIN_SLUG}/"
WP_USERNAME="wazidshah"

# Folder names — adjust if your local layout differs.
GIT_REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"   # this script lives inside the git repo
SVN_DIR="../${PLUGIN_SLUG}-svn"                                 # sibling folder, NOT inside the git repo

cmd="${1:-}"

case "$cmd" in

  setup)
    echo "→ Checking out SVN repo to ${SVN_DIR} ..."
    svn co "$SVN_URL" "$SVN_DIR"

    echo "→ Applying svn:ignore property to trunk/ ..."
    svn propset svn:ignore -F "${GIT_REPO_DIR}/svn-ignore.txt" "${SVN_DIR}/trunk"

    echo "→ Committing the ignore property ..."
    svn ci -m "Set svn:ignore for trunk" --username "$WP_USERNAME" "${SVN_DIR}/trunk"

    echo "✅ SVN setup complete. SVN checkout is at: ${SVN_DIR}"
    ;;

  sync)
    if [ ! -d "$SVN_DIR" ]; then
      echo "❌ SVN checkout not found at ${SVN_DIR}. Run './setup-svn.sh setup' first."
      exit 1
    fi

    echo "→ Syncing plugin files from Git working copy into trunk/ ..."
    rsync -av --delete \
      --exclude='.git' \
      --exclude='.gitignore' \
      --exclude='.gitattributes' \
      --exclude='.github' \
      --exclude='svn-ignore.txt' \
      --exclude='setup-svn.sh' \
      --exclude='.DS_Store' \
      --exclude='node_modules' \
      --exclude='vendor' \
      --exclude='*.zip' \
      --exclude='.env' \
      --exclude='includes/class-popy-updater.php' \
      "${GIT_REPO_DIR}/" "${SVN_DIR}/trunk/"

    echo "→ Staging new files ..."
    cd "$SVN_DIR"
    svn add trunk/* --force --quiet

    echo "→ Status:"
    svn status trunk/

    echo ""
    echo "✅ Synced. Review 'svn status' output above, then commit manually:"
    echo "   cd ${SVN_DIR} && svn ci -m \"Update description here\" --username ${WP_USERNAME}"
    ;;

  release)
    VERSION="${2:-}"
    if [ -z "$VERSION" ]; then
      echo "❌ Usage: ./setup-svn.sh release <version>   e.g. ./setup-svn.sh release 1.0.0"
      exit 1
    fi
    if [ ! -d "$SVN_DIR" ]; then
      echo "❌ SVN checkout not found at ${SVN_DIR}. Run './setup-svn.sh setup' first."
      exit 1
    fi

    cd "$SVN_DIR"
    echo "→ Tagging trunk as ${VERSION} ..."
    svn cp trunk "tags/${VERSION}"

    echo "→ Committing trunk changes + new tag ..."
    svn ci -m "Release ${VERSION}" --username "$WP_USERNAME"

    echo "✅ Released ${VERSION}. It should appear on wordpress.org within a few minutes."
    echo "   Make sure 'Stable tag: ${VERSION}' is set in readme.txt before running this."
    ;;

  *)
    echo "Usage: $0 {setup|sync|release <version>}"
    echo ""
    echo "  setup            First-time SVN checkout + apply ignore rules"
    echo "  sync             Copy plugin files from this Git repo into SVN trunk/"
    echo "  release <ver>    Tag the current trunk as a release version"
    exit 1
    ;;
esac
