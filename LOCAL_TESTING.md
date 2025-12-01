# Local Testing Guide

## The Error
```
The `url` supplied for the path (./vue-admin-panel) repository does not exist
```

This means you're trying to test the package locally using a path repository, but the path is incorrect.

## Solution 1: Use Correct Relative Path

If your Laravel project is in the same parent directory as `vue-admin-panel`:

```
/Users/jacquestredoux/PhpstormProjects/
├── composer-packages/
│   └── vue-admin-panel/  ← Your package
└── your-laravel-app/     ← Your test project
```

In your test Laravel project's `composer.json`, use:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../composer-packages/vue-admin-panel"
        }
    ],
    "require": {
        "jacquestrdx123/vue3-admin-crud": "@dev"
    }
}
```

## Solution 2: Use Absolute Path

You can use an absolute path:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/Users/jacquestredoux/PhpstormProjects/composer-packages/vue-admin-panel"
        }
    ],
    "require": {
        "jacquestrdx123/vue3-admin-crud": "@dev"
    }
}
```

## Solution 3: Install from GitHub (No Packagist Needed)

You can install directly from GitHub without waiting for Packagist:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/jacquestrdx123/vue3-admin-crud.git"
        }
    ],
    "require": {
        "jacquestrdx123/vue3-admin-crud": "dev-main"
    }
}
```

Or use SSH:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:jacquestrdx123/vue3-admin-crud.git"
        }
    ],
    "require": {
        "jacquestrdx123/vue3-admin-crud": "dev-main"
    }
}
```

Then run:
```bash
composer require jacquestrdx123/vue3-admin-crud:dev-main
```

## Solution 4: Wait for Packagist (If Already Submitted)

If you've already submitted to Packagist, wait 5-10 minutes for it to be indexed, then install:

```bash
composer require jacquestrdx123/vue3-admin-crud
```

## Quick Test Setup

1. Create a test Laravel project:
```bash
cd /Users/jacquestredoux/PhpstormProjects
laravel new test-vue-admin
cd test-vue-admin
```

2. Add to `composer.json`:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/jacquestrdx123/vue3-admin-crud.git"
        }
    ]
}
```

3. Install:
```bash
composer require jacquestrdx123/vue3-admin-crud:dev-main
php artisan vue-admin-panel:install
npm install
```

## Troubleshooting

### Path Repository Not Found
- Make sure the path is relative to your Laravel project's root
- Or use absolute path
- Check that the package directory actually exists

### Authentication Issues with GitHub
- Use HTTPS with Personal Access Token
- Or use SSH (set up SSH keys first)
- Or use path repository if both projects are local

### Package Not Found
- If using GitHub, make sure the repository is accessible
- If using Packagist, wait 5-10 minutes after submission
- Check the exact package name matches: `jacquestrdx123/vue3-admin-crud`

