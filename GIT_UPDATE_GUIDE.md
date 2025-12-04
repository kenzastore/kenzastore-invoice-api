# Git Update & Deployment Guide

## Overview
This guide covers how to update your Invoice Management System using Git for both development and production environments.

---

## Initial Git Setup

### 1. Initialize Repository (First Time Only)

```bash
cd /path/to/kenzastore.my.id
git init
git add .
git commit -m "Initial commit"
```

### 2. Connect to Remote Repository

```bash
# Add remote repository
git remote add origin https://github.com/your-username/invoice-system.git

# Push to GitHub
git push -u origin main
```

---

## Development Workflow

### Making Changes

1. **Check current status:**
```bash
git status
```

2. **Create a new branch (recommended):**
```bash
git checkout -b feature/your-feature-name
```

3. **Make your changes** to the code

4. **Stage changes:**
```bash
# Stage specific files
git add path/to/file.php

# Or stage all changes
git add .
```

5. **Commit changes:**
```bash
git commit -m "Description of changes"
```

6. **Push to GitHub:**
```bash
git push origin feature/your-feature-name
```

### Merge to Main Branch

```bash
# Switch to main branch
git checkout main

# Merge your feature branch
git merge feature/your-feature-name

# Push to GitHub
git push origin main
```

---

## Production Deployment

### Method 1: Pull from Git (Recommended)

**On Production Server:**

```bash
# Navigate to project directory
cd /var/www/html/your-domain

# Pull latest changes
git pull origin main

# Install/update dependencies (if needed)
composer install --no-dev --optimize-autoloader

# Clear cache if applicable
php artisan cache:clear  # If using Laravel
```

### Method 2: Clone Fresh (First Deployment)

```bash
# Navigate to web directory
cd /var/www/html

# Clone repository
git clone https://github.com/your-username/invoice-system.git your-domain

# Navigate to project
cd your-domain

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
chmod -R 755 .
chmod -R 777 invoices/

# Configure environment
cp .env.example .env
nano .env  # Edit with production values
```

---

## Important Files to Exclude

Create/Update `.gitignore`:

```gitignore
# Environment
.env
.env.local
.env.production

# Dependencies
/vendor/
/node_modules/

# Generated Files
/invoices/*.pdf
*.log

# Debug Files
api/debug_jwt.php
api/jwt_debug_tester.html
test_*.php
debug_*.php

# System Files
.DS_Store
Thumbs.db
*.swp
*.swo

# IDE
.idea/
.vscode/
*.sublime-*

# Temp
tmp/
temp/
*.tmp
```

---

## Post-Deployment Checklist

After pulling updates on production:

- [ ] Verify `.env` file has correct production values
- [ ] Check file permissions (755 for directories, 644 for files)
- [ ] Ensure `invoices/` directory is writable (777)
- [ ] Test database connection
- [ ] Test API endpoints
- [ ] Check error logs
- [ ] Verify PDF generation works
- [ ] Test authentication flow

---

## Common Git Commands

### Check Status
```bash
git status
git log --oneline  # View commit history
```

### View Changes
```bash
git diff  # Unstaged changes
git diff --staged  # Staged changes
```

### Undo Changes
```bash
# Discard changes in working directory
git checkout -- filename

# Unstage files
git reset HEAD filename

# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1
```

### Branch Management
```bash
# List branches
git branch

# Create new branch
git branch branch-name

# Switch branch
git checkout branch-name

# Delete branch
git branch -d branch-name
```

---

## Automated Deployment Script

Create `deploy.sh` in project root:

```bash
#!/bin/bash

# Production deployment script

echo "🚀 Starting deployment..."

# Pull latest changes
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 777 invoices/

# Clear cache (if applicable)
# php artisan cache:clear

echo "✅ Deployment complete!"

# Run verification
echo "🔍 Running verification..."
php -v
ls -la invoices/

echo "✨ Done!"
```

Make it executable:
```bash
chmod +x deploy.sh
```

Run it:
```bash
./deploy.sh
```

---

## Git Workflow Best Practices

1. **Commit Often**: Make small, frequent commits
2. **Write Clear Messages**: Use descriptive commit messages
3. **Use Branches**: Create feature branches for new work
4. **Pull Before Push**: Always pull latest changes before pushing
5. **Test Locally**: Test all changes locally before deploying
6. **Backup Database**: Backup database before major updates
7. **Review Changes**: Use `git diff` to review before committing

---

## Emergency Rollback

If deployment breaks production:

```bash
# View commit history
git log --oneline

# Rollback to previous commit
git reset --hard COMMIT_HASH

# Force push (use with caution!)
git push -f origin main
```

---

## Files That Should NEVER Be Committed

- `.env` (contains sensitive data)
- `/vendor/` (use composer install)
- `/invoices/*.pdf` (user-generated content)
- Debug files (debug_*.php, test_*.php)
- API keys and secrets
- Database backups

---

## Troubleshooting

### Permission Denied
```bash
# Fix SSH key issues
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_rsa
```

### Merge Conflicts
```bash
# View conflicts
git status

# Manually edit conflicted files
nano conflicted-file.php

# Mark as resolved
git add conflicted-file.php
git commit
```

### Detached HEAD State
```bash
# Create branch from detached state
git branch temp-save-work

# Switch back to main
git checkout main
```

---

## Quick Reference

| Task | Command |
|------|---------|
| Clone repo | `git clone <url>` |
| Check status | `git status` |
| Stage all | `git add .` |
| Commit | `git commit -m "message"` |
| Push | `git push origin main` |
| Pull | `git pull origin main` |
| New branch | `git checkout -b branch-name` |
| Merge | `git merge branch-name` |
| View log | `git log --oneline` |

---

## Additional Resources

- [Git Documentation](https://git-scm.com/doc)
- [GitHub Guides](https://guides.github.com/)
- [Git Cheat Sheet](https://education.github.com/git-cheat-sheet-education.pdf)
