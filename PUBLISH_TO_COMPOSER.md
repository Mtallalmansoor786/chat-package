# Publishing Package to Composer Repository

This guide explains how to publish your chat package so it can be installed via `composer require` like any other package.

## Option 1: Publish to Packagist (Public Package)

### Step 1: Create GitHub Repository

1. Go to GitHub and create a new repository (e.g., `chat-package`)
2. Initialize git in your package folder:

```bash
cd packages/chat-package
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/yourusername/chat-package.git
git push -u origin main
```

### Step 2: Create Git Tags (Versioning)

```bash
# Create version tag
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0
```

### Step 3: Submit to Packagist

1. Go to https://packagist.org
2. Click "Submit" 
3. Enter your repository URL: `https://github.com/yourusername/chat-package`
4. Click "Check" and then "Submit"

### Step 4: Install in Any Project

Once approved (usually instant), install in any Laravel project:

```bash
composer require chat-package/chat-package
```

**That's it!** No need to copy files or configure repositories.

---

## Option 2: Private GitHub/GitLab Repository (Private Package)

### Step 1: Create Private Repository

1. Create a private repository on GitHub/GitLab
2. Push your package code (same as Step 1 above)

### Step 2: Get Access Token

**For GitHub:**
1. Go to Settings → Developer settings → Personal access tokens
2. Create token with `repo` scope
3. Copy the token

**For GitLab:**
1. Go to Settings → Access Tokens
2. Create token with `read_api` scope
3. Copy the token

### Step 3: Configure Composer in Target Project

Add to `composer.json` in the project where you want to use it:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/yourusername/chat-package.git"
        }
    ],
    "require": {
        "chat-package/chat-package": "^1.0"
    }
}
```

### Step 4: Configure Authentication

**For GitHub:**
Create `auth.json` in project root (or use global config):

```json
{
    "github-oauth": {
        "github.com": "your_personal_access_token"
    }
}
```

**For GitLab:**
```json
{
    "gitlab-token": {
        "gitlab.com": "your_access_token"
    }
}
```

Or use environment variable:
```bash
export COMPOSER_AUTH='{"gitlab-token":{"gitlab.com":"your_token"}}'
```

### Step 5: Install

```bash
composer require chat-package/chat-package
```

---

## Option 3: Self-Hosted Satis (Private Package Server)

If you want your own private package server:

### Step 1: Install Satis

```bash
composer create-project composer/satis --stability=dev
```

### Step 2: Create satis.json

```json
{
    "name": "Your Private Package Repository",
    "homepage": "https://packages.yourdomain.com",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/yourusername/chat-package.git"
        }
    ],
    "require": {
        "chat-package/chat-package": "*"
    }
}
```

### Step 3: Build Repository

```bash
php bin/satis build satis.json public/
```

### Step 4: Configure in Target Project

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://packages.yourdomain.com"
        }
    ],
    "require": {
        "chat-package/chat-package": "*"
    }
}
```

---

## Versioning Best Practices

### Semantic Versioning

Use semantic versioning (MAJOR.MINOR.PATCH):

- **MAJOR** (1.0.0): Breaking changes
- **MINOR** (1.1.0): New features, backward compatible
- **PATCH** (1.0.1): Bug fixes, backward compatible

### Creating Versions

```bash
# Create version tag
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0

# For updates
git tag -a v1.0.1 -m "Bug fixes"
git push origin v1.0.1
```

### Update composer.json Version

You can also specify version in `composer.json`:

```json
{
    "version": "1.0.0"
}
```

But Git tags are preferred.

---

## Recommended: Packagist (Easiest)

**For public packages, Packagist is the easiest:**

1. ✅ No authentication needed
2. ✅ Automatic updates when you push tags
3. ✅ Works with `composer require` directly
4. ✅ Free for public packages
5. ✅ Fast CDN distribution

**Steps:**
1. Push to GitHub
2. Submit to Packagist
3. Done! Anyone can install with `composer require chat-package/chat-package`

---

## Installation After Publishing

Once published, installation is simple:

```bash
# For Packagist (public)
composer require chat-package/chat-package

# For private repository
composer require chat-package/chat-package --repository-url=https://github.com/yourusername/chat-package.git
```

Then follow the standard installation steps:
1. Add Pusher to `.env`
2. Run `php artisan migrate`
3. Access `/chat`

---

## Updating the Package

### For Packagist:

1. Make changes
2. Commit and push
3. Create new tag: `git tag -a v1.0.1 -m "Update"`
4. Push tag: `git push origin v1.0.1`
5. Packagist auto-updates (or click "Update" on Packagist)

### For Private Repos:

1. Make changes
2. Commit and push
3. Create new tag
4. Users run: `composer update chat-package/chat-package`

---

## Package Name Convention

For Packagist, use format: `vendor-name/package-name`

Examples:
- `yourname/chat-package`
- `yourcompany/laravel-chat`
- `chat-package/chat-package`

**Note:** Update `composer.json` name before publishing:
```json
{
    "name": "yourname/chat-package"
}
```

---

## Checklist Before Publishing

- [ ] Update `composer.json` with correct name
- [ ] Add proper description and keywords
- [ ] Ensure all dependencies are listed
- [ ] Create `.gitignore` file
- [ ] Add README.md with installation instructions
- [ ] Test package installation locally
- [ ] Create initial version tag (v1.0.0)
- [ ] Push to Git repository
- [ ] Submit to Packagist (if public) or configure private repo

---

## Quick Start (Packagist)

```bash
# 1. Initialize git
cd packages/chat-package
git init
git add .
git commit -m "Initial commit"

# 2. Create GitHub repo and push
git remote add origin https://github.com/yourusername/chat-package.git
git push -u origin main

# 3. Create version tag
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0

# 4. Submit to Packagist
# Go to https://packagist.org and submit your repo URL

# 5. Install anywhere
composer require yourname/chat-package
```

That's it! Your package is now installable like any other Composer package! 🎉

