# Quick Setup: Make Package Installable via Composer

## Fastest Way (5 Minutes)

### Step 1: Update Package Name

Edit `composer.json` and change the name to your preferred format:

```json
{
    "name": "yourname/chat-package",
    ...
}
```

**Examples:**
- `yourname/chat-package`
- `yourcompany/laravel-chat`
- `yourusername/chat-package`

### Step 2: Push to GitHub

```bash
cd packages/chat-package

# Initialize git (if not already)
git init
git add .
git commit -m "Initial commit"

# Create GitHub repository, then:
git remote add origin https://github.com/yourusername/chat-package.git
git branch -M main
git push -u origin main
```

### Step 3: Create Version Tag

```bash
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0
```

### Step 4: Submit to Packagist

1. Go to https://packagist.org
2. Click "Submit"
3. Paste your GitHub URL: `https://github.com/yourusername/chat-package`
4. Click "Check" then "Submit"

### Step 5: Install Anywhere!

```bash
composer require yourname/chat-package
```

**Done!** Your package is now installable like any other Composer package.

---

## For Private Packages

If you want to keep it private, see `PUBLISH_TO_COMPOSER.md` for private repository setup.

---

## After Publishing

Users can install with:

```bash
composer require yourname/chat-package
```

Then they just need to:
1. Add Pusher credentials to `.env`
2. Run `php artisan migrate`
3. Access `/chat`

No copying files, no manual setup - just like Laravel Breeze or any other package!

