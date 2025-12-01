<?php

/**
 * Post-install script for Vue Admin Panel
 * This script runs automatically after composer install/update
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     Vue Admin Panel - Installation Instructions         ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "📦 Package installed successfully!\n";
echo "\n";
echo "Next steps:\n";
echo "\n";
echo "1. Run the installer command:\n";
echo "   php artisan vue-admin-panel:install\n";
echo "\n";
echo "   This will:\n";
echo "   - Merge npm dependencies into your package.json\n";
echo "   - Publish Tailwind CSS 4 configuration\n";
echo "   - Publish all package assets (components, config, migrations)\n";
echo "   - Optionally run npm install\n";
echo "\n";
echo "2. Or manually install:\n";
echo "   - npm install\n";
echo "   - php artisan vendor:publish --tag=inertia-resource\n";
echo "\n";
echo "📚 Full documentation: See README.md\n";
echo "\n";
