#!/bin/bash

# Quick test script for local package development
# This script copies package files to the test project's vendor folder
# Run this after making changes to test them quickly

PACKAGE_DIR="/Users/jacquestredoux/PhpstormProjects/composer-packages/vue-admin-panel"
TEST_PROJECT="/Users/jacquestredoux/PhpstormProjects/composer-packages/vue-admin-panel/fresh-laravel-12"
VENDOR_DIR="$TEST_PROJECT/vendor/jacquestrdx123/vue3-admin-crud"

echo "🚀 Copying package files to test project..."

# Remove old vendor copy
if [ -d "$VENDOR_DIR" ]; then
    echo "📦 Removing old vendor copy..."
    rm -rf "$VENDOR_DIR"
fi

# Copy package files
echo "📋 Copying package source..."
mkdir -p "$VENDOR_DIR"
cp -r "$PACKAGE_DIR/src" "$VENDOR_DIR/"
cp -r "$PACKAGE_DIR/config" "$VENDOR_DIR/" 2>/dev/null || true
cp -r "$PACKAGE_DIR/database" "$VENDOR_DIR/" 2>/dev/null || true
cp -r "$PACKAGE_DIR/resources" "$VENDOR_DIR/" 2>/dev/null || true
cp -r "$PACKAGE_DIR/stubs" "$VENDOR_DIR/" 2>/dev/null || true
cp "$PACKAGE_DIR/composer.json" "$VENDOR_DIR/" 2>/dev/null || true

# Regenerate autoload
echo "🔄 Regenerating autoload..."
cd "$TEST_PROJECT"
composer dump-autoload

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "✅ Done! Your changes should now be available in the test project."
echo "💡 Tip: Run this script after making changes to the package."
