#!/usr/bin/env bash
#
# Ensure a draft GitHub release exists for the current app version, so that
# `php artisan native:publish <os>` (or `native:build --publish`) has a release
# to upload its artifacts into. electron-builder reuses an existing draft, but
# will not create one from a local, non-tagged build.
#
# Everything is read from .env -- nothing about the repo is hard-coded:
#   GITHUB_OWNER, GITHUB_REPO   -> the target repository (owner/repo)
#   NATIVEPHP_APP_VERSION       -> the version; the tag is "v<version>"
#
# If GITHUB_OWNER/GITHUB_REPO (or the version) are not set, this is a no-op,
# so the build still works for anyone who hasn't configured publishing.
#
# NOTE: keep this file ASCII-only. NativePHP runs prebuild hooks through a
# subprocess whose locale may be C/POSIX, where multibyte characters break
# shell parsing.
#
# Requires the GitHub CLI (`gh`) to be installed and authenticated.

set -eu

ENV_FILE="${1:-.env}"

# Read a KEY=value from the .env file, stripping surrounding quotes/whitespace.
get_env() {
    local line val
    line="$(grep -E "^$1=" "$ENV_FILE" 2>/dev/null | head -n1 || true)"
    val="${line#*=}"
    val="${val%\"}"; val="${val#\"}"     # strip surrounding double quotes
    val="${val%\'}"; val="${val#\'}"     # strip surrounding single quotes
    printf '%s' "$val" | tr -d '[:space:]'
}

if [ ! -f "$ENV_FILE" ]; then
    echo "-> $ENV_FILE not found; skipping draft release creation."
    exit 0
fi

OWNER="$(get_env GITHUB_OWNER)"
REPO="$(get_env GITHUB_REPO)"
VER="$(get_env NATIVEPHP_APP_VERSION)"

if [ -z "$OWNER" ] || [ -z "$REPO" ]; then
    echo "-> GITHUB_OWNER/GITHUB_REPO not set in $ENV_FILE; skipping draft release creation."
    exit 0
fi

if [ -z "$VER" ]; then
    echo "-> NATIVEPHP_APP_VERSION not set in $ENV_FILE; skipping draft release creation."
    exit 0
fi

TAG="v${VER#v}"          # normalize to exactly one leading "v"
SLUG="$OWNER/$REPO"

if gh release view "$TAG" --repo "$SLUG" >/dev/null 2>&1; then
    echo "-> Release $TAG already exists on $SLUG; nothing to do."
else
    echo "-> Creating draft release $TAG on $SLUG ..."
    gh release create "$TAG" --repo "$SLUG" --draft --title "$TAG" --generate-notes
fi
