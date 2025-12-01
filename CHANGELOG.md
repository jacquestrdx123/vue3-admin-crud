# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2024-12-XX

### Added
- Full Vite 5.4.0 support with laravel-vite-plugin@^1.0.0
- Automatic vite.config.js import detection and fixing
- Enhanced InstallCommand with automatic npm install
- Improved error handling and fallback mechanisms

### Changed
- **BREAKING**: Updated to Vite 5.4.0 (from Vite 4.5.0)
- **BREAKING**: Updated laravel-vite-plugin to ^1.0.0 (from 0.7.2)
- vite.config.js stub now uses `import laravel, { refreshPaths } from 'laravel-vite-plugin'`
- vite.config.js now uses `refresh: refreshPaths` instead of `refresh: true`
- InstallCommand automatically runs npm install without confirmation
- InstallCommand automatically fixes incorrect vite.config.js imports

### Fixed
- Fixed vite.config.js import detection to correctly identify and fix `@laravel/vite-plugin` (incorrect) to `laravel-vite-plugin` (correct)
- Fixed vite.config.js import detection to handle old `laravel/vite-plugin` pattern
- Resolved "ERESOLVE unable to resolve dependency tree" errors
- Added fallback to use `--legacy-peer-deps` if npm install fails initially

## [1.1.5] - 2024-12-XX

### Fixed

- Updated to Vite 5.4.0 with laravel-vite-plugin@^1.0.0 for full Vite 5 support
- Updated vite.config.js stub to use correct import: `import laravel, { refreshPaths } from 'laravel-vite-plugin'`
- Updated vite.config.js to use `refresh: refreshPaths` instead of `refresh: true`
- Added fallback to use `--legacy-peer-deps` if npm install fails initially
- Resolved "ERESOLVE unable to resolve dependency tree" error during npm install

## [1.1.4] - 2024-12-XX

### Fixed

- Fixed vite.config.js import: automatically detects and fixes incorrect `import laravel from 'laravel-vite-plugin'` to correct `import laravel from 'laravel/vite-plugin'`
- Added vite.config.js stub to publishable assets
- InstallCommand now automatically fixes incorrect vite.config.js imports during installation

## [1.1.3] - 2024-12-XX

### Fixed

- Fixed incorrect package name: changed from `@laravel/vite-plugin` (doesn't exist) to `laravel-vite-plugin` (correct package)
- InstallCommand now runs `npm install` automatically without confirmation
- Improved error messages and warnings for Vite configuration

## [1.1.2] - 2024-12-XX

### Fixed

- Fixed missing `@laravel/vite-plugin` dependency in package.json and InstallCommand
- Resolved "Cannot find package 'laravel'" error when running `npm run build`

## [1.1.1] - 2024-12-XX

### Added

- Documentation for republishing assets after package updates
- `vue-admin-panel:publish` command for easy asset republishing

## [1.1.0] - 2024-12-XX

### Added

- `make:inertia-resource` Artisan command to generate InertiaResource components
- Command supports flags: `--controller`, `--routes`, `--vue`, and `--all`
- Stub files for InertiaResource, Controller, Routes, and Vue pages (Index, Create, Edit, Show)
- Automatic generation of proper namespaces, paths, and route definitions
- Model validation before generation
- File overwrite protection with confirmation prompts

### Changed

- Service provider now registers the new `CreateInertiaResourceCommand`

## [1.0.0] - 2024-XX-XX

### Added

- Initial release of InertiaResource package
- Complete CRUD resource system for Inertia.js applications
- Table components with columns, filters, sorting, and pagination
- Form components with various field types
- Action system (create, edit, show, delete, bulk actions)
- Column preference system for user customization
- Navigation system for resource discovery
- Vue component library (tables, forms, UI components)
- Service provider with asset publishing
- Configuration system
- Migration stubs for column preferences
- Testing infrastructure with Pest PHP
- Comprehensive documentation

### Features

- **Columns**: Text, Money, Date, Boolean, Badge, Link, Array, JSON, Percentage
- **Form Fields**: Text, Textarea, Select, MultiSelect, Number, Date, DateTime, Time, Checkbox, Toggle, FileUpload, RichEditor, Repeater, Relationship, GoogleMaps, Masked, AnchorTag
- **Filters**: Select, Boolean, Date, Number, Custom, Trashed
- **Actions**: Create, Edit, Show, Delete, Archive, Export, Bulk Actions, Ajax Actions, New Tab Actions
- **Table Features**: Search, Sorting, Pagination, Column Visibility, Column Reordering, Bulk Selection, Filters

## [1.0.0] - 2024-XX-XX

### Added

- Initial stable release
