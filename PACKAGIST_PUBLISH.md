# Publishing to Packagist - Step by Step Guide

## Prerequisites Checklist

- [x] Package name: `jacquestrdx123/vue-inertia-resources`
- [x] Composer.json is valid
- [x] GitHub repository exists: `jacquestrdx123/vue3-admin-crud`
- [ ] All changes committed
- [ ] Version tag created
- [ ] Changes pushed to GitHub

## Step 1: Commit All Changes

Before publishing, make sure all your changes are committed:

```bash
# Review what will be committed
git status

# Add all changes
git add .

# Commit with a descriptive message
git commit -m "Prepare for Packagist release v2.7.3"
```

## Step 2: Create and Push Version Tag

Packagist uses git tags to determine package versions:

```bash
# Create an annotated tag for version 2.7.3
git tag -a v2.7.3 -m "Release version 2.7.3"

# Push the tag to GitHub
git push origin v2.7.3

# Also push your commits
git push origin main
```

## Step 3: Submit to Packagist

1. **Go to Packagist**: Visit [https://packagist.org](https://packagist.org)

2. **Sign up/Login**:

   - Click "Sign up" or "Log in"
   - Use "Login with GitHub" (recommended) to connect your GitHub account

3. **Submit Your Package**:

   - Click the "Submit" button (top right)
   - Enter your repository URL: `https://github.com/jacquestrdx123/vue3-admin-crud`
   - Click "Check" to verify the package
   - Click "Submit" to publish

4. **Wait for Processing**:
   - Packagist will automatically detect your package
   - It may take a few minutes to process
   - You'll see a success message when it's ready

## Step 4: Verify Installation

Once published, test that others can install it:

```bash
# In a fresh Laravel project, test installation
composer require jacquestrdx123/vue-inertia-resources
```

## Step 5: Set Up Auto-Update (Recommended)

Packagist can automatically update when you push new tags:

1. Go to your package page on Packagist
2. Click "Settings" (gear icon)
3. Enable "GitHub Service Hook" or "Update Hook"
4. Copy the webhook URL
5. Go to your GitHub repository → Settings → Webhooks
6. Add a new webhook:
   - Payload URL: Paste the Packagist webhook URL
   - Content type: `application/json`
   - Events: Select "Just the push event"
   - Click "Add webhook"

Now, whenever you push a new tag, Packagist will automatically update!

## Future Releases

For future versions:

1. Update version in `composer.json` (optional - Packagist uses tags)
2. Commit changes:
   ```bash
   git add .
   git commit -m "Release v2.7.4"
   ```
3. Create and push new tag:
   ```bash
   git tag -a v2.7.4 -m "Release version 2.7.4"
   git push origin v2.7.4
   ```
4. Packagist will auto-update (if webhook is configured)

## Important Notes

- **Package Name**: Your package name is `jacquestrdx123/vue-inertia-resources` (must match your Packagist username)
- **Repository URL**: Your GitHub repo is `vue3-admin-crud` - this is fine, the names don't need to match
- **Version Tags**: Use semantic versioning (v2.7.3, v2.7.4, v2.8.0, etc.)
- **Version Field**: The `version` field in composer.json is optional for Packagist packages (it uses git tags instead)

## Troubleshooting

### Package Not Found After Submission

- Wait a few minutes for Packagist to process
- Check that your tag was pushed: `git ls-remote --tags origin`
- Verify the repository URL is correct

### Auto-Update Not Working

- Check webhook configuration in GitHub
- Verify the webhook URL in Packagist settings
- Check GitHub webhook delivery logs for errors

### Composer Install Fails

- Ensure all dependencies are correctly specified in `composer.json`
- Check PHP version requirements
- Verify Laravel version compatibility
