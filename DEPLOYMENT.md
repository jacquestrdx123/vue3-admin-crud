# Deployment Guide

This guide explains how to make the `jacquestrdx/vue3-admin-crud` package available via Composer.

## Option 1: Publish to Packagist (Recommended for Public Packages)

Packagist is the main repository for PHP packages. This is the easiest way to make your package publicly available.

### Step 1: Create a GitHub Repository

1. Create a new repository on GitHub (e.g., `vue3-admin-crud`)
2. Push your code to GitHub:

```bash
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/jacquestrdx/vue3-admin-crud.git
git branch -M main
git push -u origin main
```

### Step 2: Create a Packagist Account

1. Go to [packagist.org](https://packagist.org)
2. Click "Sign up" and create an account using your GitHub account (recommended)
3. Verify your email address

### Step 3: Submit Your Package

1. Log into Packagist
2. Click "Submit" in the top navigation
3. Paste your repository URL (e.g., `https://github.com/jacquestrdx/vue3-admin-crud`)
4. Click "Check"
5. If everything is valid, click "Submit"

### Step 4: Set Up Auto-Update (Recommended)

1. Go to your package page on Packagist
2. Click "Settings"
3. Enable "Auto-update (GitHub Service Hook)"
4. Create a GitHub Service Hook:
   - Go to your GitHub repository
   - Settings → Webhooks → Add webhook
   - Payload URL: `https://packagist.org/api/github?username=jacquestrdx`
   - Content type: `application/json`
   - Secret: (get from Packagist settings)
   - Events: Select "Just the push event"

### Step 5: Create a Release Tag

For stable releases, create a git tag:

```bash
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0
```

Or create a release on GitHub:
1. Go to your repository on GitHub
2. Click "Releases" → "Create a new release"
3. Choose a tag (e.g., `v1.0.0`)
4. Add release notes
5. Click "Publish release"

### Step 6: Verify Installation

Once your package is on Packagist, users can install it:

```bash
composer require jacquestrdx/vue3-admin-crud
```

## Option 2: Private Git Repository (For Private Packages)

If you want to keep your package private, you can use a Git repository directly.

### Step 1: Push to Private Repository

Push your code to GitHub, GitLab, or Bitbucket as a private repository.

### Step 2: Users Add Repository to composer.json

Users need to add your repository to their `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/jacquestrdx/vue3-admin-crud.git"
        }
    ],
    "require": {
        "jacquestrdx/vue3-admin-crud": "^1.0"
    }
}
```

### Step 3: Authentication (If Private)

For private repositories, users need to authenticate:

**GitHub Personal Access Token:**
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/jacquestrdx/vue3-admin-crud.git"
        }
    ],
    "config": {
        "github-oauth": {
            "github.com": "YOUR_GITHUB_TOKEN"
        }
    }
}
```

Or use SSH:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:jacquestrdx/vue3-admin-crud.git"
        }
    ]
}
```

## Option 3: Private Packagist (For Organizations)

If you have a Private Packagist subscription, you can:
1. Create an organization on Private Packagist
2. Add your Git repository
3. Users authenticate with your Private Packagist instance

## Versioning

Follow [Semantic Versioning](https://semver.org/):
- `1.0.0` - Initial stable release
- `1.0.1` - Bug fixes (patch)
- `1.1.0` - New features (minor)
- `2.0.0` - Breaking changes (major)

Create tags for each version:
```bash
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0
```

## Pre-Release Checklist

Before publishing, ensure:

- [ ] Package name in `composer.json` is correct
- [ ] All dependencies are properly listed
- [ ] README.md is complete and accurate
- [ ] All tests pass: `composer test`
- [ ] Version number is set correctly
- [ ] `.gitattributes` is configured for clean releases
- [ ] No sensitive information is committed
- [ ] License file exists if specified in composer.json

## Testing Before Publishing

Test installation locally using a path repository:

1. In a test Laravel project, add to `composer.json`:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../vue-admin-panel"
        }
    ]
}
```

2. Install:
```bash
composer require jacquestrdx/vue3-admin-crud:@dev
```

3. Test that everything works correctly.

## Troubleshooting

### Package Not Found After Publishing

- Wait a few minutes for Packagist to index
- Check that your repository is public (if using Packagist)
- Verify the package name matches exactly

### Auto-update Not Working

- Check webhook is configured correctly
- Verify secret matches in both Packagist and GitHub
- Check webhook delivery logs in GitHub

### Composer Requires Dev Version

Update `composer.json`:
```json
{
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

And ensure you have stable release tags (not just dev branches).

