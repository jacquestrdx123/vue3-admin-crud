# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.4.36] - 2024-12-XX

### Fixed

- Fixed fatal error in `updateCustomersConfig` method when package config file is not found - changed bare `return;` to `return false;` to satisfy return type requirement

## [2.4.35] - 2024-12-XX

### Added

- Added ResourceMenuSeeder that automatically creates "Administration" menu group and menu items for UserResource and CustomerResource
- Seeder creates menu items only for resources that were selected during installation
- Automatic seeder execution after resource creation during installation

### Fixed

- Fixed seeder creation to use single ResourceMenuSeeder instead of separate seeders
- Fixed variable scope issue for tracking resource creation across methods

### Improved

- Seeders now check for route existence to determine which menu items to create
- Better duplicate prevention for menu groups and items using firstOrCreate()
- Migrations are automatically checked and run before seeders execute

## [2.4.34] - 2024-12-XX

### Fixed

- Fixed menu item routes to be properly added to routes/admin.php inside protected middleware group
- Fixed route generation to insert routes with correct indentation inside admin routes group
- Updated menu system next steps to reference /admin/ routes instead of /vue/
- Fixed AdminLayout.vue to always update during installation (was being skipped if file existed)

### Changed

- Standardized UserResource question to match Customer Resource question format
- Updated AdminLayout to include Menu Groups and Menu Items links in user dropdown menu
- Added menu items from MenuBuilder config to AdminLayout user dropdown
- AdminLayout.vue now always updates during installation to ensure users get latest changes

### Improved

- Added route verification after menu system creation to confirm routes were added successfully
- Better error handling and user feedback during resource creation

## [2.4.33] - 2024-12-XX

### Changed

- Updated TopRightMenu: Renamed "Menu Items" to "Menu Groups" for better clarity
- Improved menu navigation with clearer labeling

## [2.4.32] - 2024-12-XX

### Changed

- Optimized InstallCommand with improved menu system setup
- Changed User Resource question default to "yes" during installation
- Added migration existence checks before creating them to prevent duplicates
- Added question to create Menu Groups and Items during installation
- Menu system setup now automatically creates models, migrations, routes, and Vue files

### Improved

- Better user feedback during installation process
- Clearer next steps guidance after menu system creation

## [2.4.31] - 2024-12-XX

### Added

- Database-driven menu system with MenuGroup and MenuItem models
- MenuBuilder helper class (`InertiaResource\Inertia\MenuBuilder`) to build menu structure from database
- MenuBuilder Vue component with drag-and-drop functionality for reordering menu groups and items
- CreateMenuModelsCommand (`vue-admin-panel:create-menu-models`) to generate menu models and migrations
- Menu configuration options: `menu_group_model`, `menu_item_model`, `menu_show_items_without_permission`
- Support for permission-based menu item visibility
- Support for hierarchical menu items with parent/child relationships
- Integration of MenuBuilder into TopRightMenu for easy access

### Changed

- ServiceProvider now publishes menu migrations and model stubs
- MenuBuilder automatically checks user permissions and filters menu items accordingly

## [2.4.6] - 2024-12-XX

### Added

- Added missing UI components as stubs: Card.vue, Badge.vue, Pagination.vue
- Added useFieldVisibility.js composable stub for conditional field visibility
- Added FunkyLoader.vue and Drawer.vue to resources/js/UI/ for automatic publishing
- InstallCommand now automatically creates UI components and composables during installation
- Components are created in: `resources/js/Components/UI/` and `resources/js/Composables/`

### Fixed

- Fixed BaseDataTable import paths for FunkyLoader and Drawer to use vendor path
- Changed imports from `../UI/` to `@/vendor/inertia-resource/UI/` for proper resolution
- Resolved build errors caused by missing UI components

### Changed

- InstallCommand now includes `createUIComponents()` method that creates/updates UI components
- UI components are always updated during installation to ensure they exist

## [2.4.5] - 2024-12-XX

### Fixed

- Fixed Dashboard.vue not being created/updated during installation
- InstallCommand now always ensures Dashboard.vue exists (updates if already exists)
- Resolved "Page not found: ./Pages/Dashboard.vue" error

## [2.4.4] - 2024-12-XX

### Added

- Added `/admin` route that renders the Dashboard page
- Dashboard route is protected by `auth` middleware
- Route name: `admin.dashboard`

### Changed

- Updated `createAdminRoutes()` to include protected dashboard route
- Login redirect now properly routes to `/admin` dashboard

## [2.4.3] - 2024-12-XX

### Added

- Added generic `AdminLayout.vue` component with sidebar navigation, top bar, and user menu
- Added `DashboardLayout.vue` component that wraps AdminLayout with dashboard-specific features
- Added sample `Dashboard.vue` page with stat cards, recent activity, and quick actions
- Added `StatCard.vue` component for displaying metrics with icons, colors, and change indicators
- InstallCommand now automatically creates admin layouts and dashboard during installation
- Added `inertia-resource-layouts` publish tag for layout files
- Comprehensive documentation for admin layouts and dashboard in README

### Changed

- InstallCommand now creates layout and dashboard files automatically
- Updated README with Admin Layout and Dashboard section including usage examples

## [2.4.2] - 2024-12-XX

### Changed

- Updated `vue-admin-panel:make-user` command to create a user instance in the database instead of generating a User model
- Command now interactively prompts for:
  - Name
  - Email
  - Password (with hidden input)
  - Password confirmation (with hidden input)
  - Email verification status
- Added validation for all user fields including password confirmation matching
- Command displays a summary table with created user information

### Fixed

- Corrected command functionality: now creates user database records instead of model files

## [2.4.1] - 2024-12-XX

### Added

- Added `vue-admin-panel:make-user` artisan command to create a User model with all fields and password confirmation
- New `CreateUserModelCommand` that generates a complete User model with:
  - Standard authentication fields (name, email, password)
  - Email verification support
  - Remember token support
  - Proper fillable, hidden, and casts arrays
  - Password hashing via Laravel's 'hashed' cast
  - Documentation for password confirmation validation
- New `UserModel.stub` template for User model generation
- Command supports `--force` option to overwrite existing User models

## [2.4.0] - 2024-12-XX

### Added

- Added comprehensive documentation for `make:inertia-resource` command in README
- Documented all command options: `--controller`, `--routes`, `--vue`, and `--all`
- Added examples showing what gets generated for each option

### Changed

- Route generation now wraps all routes in `/admin` prefix for consistency with admin authentication
- Route names changed from `vue.{slug}.{action}` to `admin.{slug}.{action}` pattern
- Updated `routes.stub` to automatically wrap routes in `Route::prefix('admin')->name('admin.')`
- Updated `CreateInertiaResourceCommand` to generate routes with admin prefix
- Updated README examples to show correct admin prefix structure

### Fixed

- Route generation now properly matches admin authentication structure
- Route name generation now uses `admin.` prefix instead of `vue.` prefix

## [2.3.0] - 2024-12-XX

### Fixed

- Fixed `app.js.stub` to use `resolvePageComponent` from `laravel-vite-plugin/inertia-helpers` (recommended approach)
- Fixed `AdminLogin.vue.stub` to use direct URL `/admin/login` instead of `route('admin.login')` (removes Ziggy dependency)
- Fixed `CustomerLogin.vue.stub` to use direct URL `/login` instead of `route('customer.login')` (removes Ziggy dependency)

### Added

- InstallCommand now automatically detects and fixes existing `app.js` files missing Inertia setup
- InstallCommand now automatically detects and fixes existing login pages using `route()` helper
- Enhanced file detection to prevent white page issues on existing installations

### Changed

- `createJavaScriptEntryPoint()` now checks for proper Inertia setup and fixes missing configurations
- `createLoginPages()` now checks for `route()` helper usage and automatically fixes to use direct URLs

## [2.2.0] - 2024-12-XX

### Added

- InstallCommand now prompts user during installation whether they want to use Customers
- Automatic configuration of `use_customers` setting in `config/inertia-resource.php` based on user's choice
- `updateCustomersConfig()` method to handle customer configuration updates during installation

### Changed

- Customer login page creation now respects the `use_customers` configuration setting set during installation

## [2.1.1] - 2024-12-XX

### Fixed

- Fixed "View [app] not found" error by automatically creating Inertia root template (`resources/views/app.blade.php`) during installation
- Added automatic creation of JavaScript entry point (`resources/js/app.js`) during installation
- Added automatic creation of `resources/js/bootstrap.js` for axios setup
- Added automatic creation of `resources/css/app.css` with Tailwind import if missing

### Added

- New stubs: `app.blade.php.stub` and `app.js.stub` for Inertia setup
- InstallCommand now creates all required Inertia files automatically:
  - `resources/views/app.blade.php` (Inertia root template)
  - `resources/js/app.js` (JavaScript entry point)
  - `resources/js/bootstrap.js` (Axios setup)
  - `resources/css/app.css` (CSS entry point with Tailwind)

## [2.1.0] - 2024-12-XX

### Fixed

- Fixed vite.config.js detection to properly handle Laravel 12's default configuration
- Added detection for missing `{ refreshPaths }` import when `laravel-vite-plugin` is imported
- Added automatic detection and addition of Vue plugin if missing from vite.config.js
- Added automatic detection and addition of resolve alias (`'@': '/resources/js'`) if missing
- Improved detection for `refresh: refreshPaths` usage without proper import
- Fixed issue where existing vite.config.js files weren't being properly updated with required configurations

### Changed

- Enhanced `fixViteConfig()` method to handle more edge cases and missing configurations
- Improved error messages and warnings for vite.config.js fixes

## [2.0.1] - 2024-12-XX

### Added

- Automatic admin routes generation during installation
- Admin authentication routes with `/admin` prefix group
- `GET /admin/login` route for displaying admin login page
- `POST /admin/login` route for handling admin authentication
- Admin login page creation (`resources/js/Pages/Auth/AdminLogin.vue`)
- Comprehensive admin authentication documentation in README

### Changed

- InstallCommand now automatically creates admin routes in `routes/web.php`
- Updated README with admin authentication section and examples
- Updated vite.config.js example in README to use correct `refreshPaths` import

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
