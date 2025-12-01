# Setting Up GitHub Repository

## The Issue
The repository doesn't exist on GitHub yet. You need to create it first before pushing.

## Option 1: Create Repository via GitHub Website (Easiest)

1. **Go to GitHub:**
   - Visit [github.com/new](https://github.com/new)
   - Or click the "+" icon → "New repository"

2. **Create the Repository:**
   - Repository name: `vue3-admin-crud`
   - Description: "A Filament-like resource system for Inertia.js applications with Vue 3 and Tailwind CSS 4"
   - Visibility: Choose Public or Private
   - **DO NOT** initialize with README, .gitignore, or license (you already have these)
   - Click "Create repository"

3. **Push Your Code:**
   After creating the repository, run these commands:

   ```bash
   git push -u origin master
   ```

   Or if GitHub shows a different branch name (like `main`), you can rename your branch:

   ```bash
   git branch -M main
   git push -u origin main
   ```

## Option 2: Use GitHub CLI (If Installed)

If you have GitHub CLI installed:

```bash
gh repo create vue3-admin-crud --public --source=. --remote=origin --push
```

## Option 3: Create Repository with Different Name

If the repository name `vue3-admin-crud` is already taken:

1. Choose a different name (e.g., `vue3-admin-crud-laravel`)
2. Update the remote URL:
   ```bash
   git remote set-url origin https://github.com/jacquestrdx/YOUR-REPO-NAME.git
   ```
3. Create the repository on GitHub with the new name
4. Push your code

## After Repository is Created

Once the repository exists on GitHub:

1. **Push your code:**
   ```bash
   git push -u origin master
   ```

2. **Create your first release:**
   ```bash
   git tag -a v1.0.0 -m "Version 1.0.0"
   git push origin v1.0.0
   ```

3. **Submit to Packagist:**
   - Go to [packagist.org](https://packagist.org)
   - Submit: `https://github.com/jacquestrdx/vue3-admin-crud`

## Troubleshooting

### Authentication Issues

If you get authentication errors:

**Option 1: Use Personal Access Token**
1. Go to GitHub Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate a new token with `repo` scope
3. Use it as password when prompted, or set it:

```bash
git remote set-url origin https://YOUR_TOKEN@github.com/jacquestrdx/vue3-admin-crud.git
```

**Option 2: Use SSH**
```bash
git remote set-url origin git@github.com:jacquestrdx/vue3-admin-crud.git
```

**Option 3: Configure Git Credentials**
```bash
git config --global credential.helper store
# Then push, enter username and token when prompted
```

