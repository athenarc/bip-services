# Deployment Setup Guide

This guide explains how to configure the automated deployment workflow that runs when code is merged to the `main` branch.

## Overview

The deployment workflow (`.github/workflows/deploy.yml`) automatically:
1. Pulls the latest code from the `main` branch
2. Installs/updates PHP dependencies via Composer
3. Runs database migrations
4. Clears application cache
5. Sets proper file permissions

## Required GitHub Secrets

You need to configure the following secrets in your GitHub repository settings:

1. Go to: **Settings** → **Secrets and variables** → **Actions** → **New repository secret**

2. Add the following secrets:

### Required Secrets

- **`SSH_HOST`**: The IP address or hostname of your server
  - Example: `192.168.1.100` or `deploy.example.com`

- **`SSH_USER`**: The username for SSH connection
  - Example: `deploy` or `www-data`

- **`SSH_KEY`**: The private SSH key for authentication
  - Generate a key pair if needed: `ssh-keygen -t ed25519 -C "github-actions"`
  - Copy the **private key** content (including `-----BEGIN` and `-----END` lines)
  - Add the **public key** to your server's `~/.ssh/authorized_keys`

### Optional Secrets

- **`SSH_PORT`**: SSH port (defaults to 22 if not set)
  - Example: `2222`

- **`DEPLOY_PATH`**: Path to your project on the server (defaults to `/data/www/sbip` if not set)
  - Example: `/var/www/html/sbip`

## Server Setup

### 1. Create SSH Key Pair (if needed)

On your local machine or CI server:
```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions_deploy
```

### 2. Add Public Key to Server

Copy the public key to your server:
```bash
ssh-copy-id -i ~/.ssh/github_actions_deploy.pub user@your-server
```

Or manually add to `~/.ssh/authorized_keys` on the server:
```bash
cat ~/.ssh/github_actions_deploy.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

### 3. Add Private Key to GitHub Secrets

Copy the private key content:
```bash
cat ~/.ssh/github_actions_deploy
```

Paste the entire output (including `-----BEGIN OPENSSH PRIVATE KEY-----` and `-----END OPENSSH PRIVATE KEY-----`) into the `SSH_KEY` secret in GitHub.

### 4. Verify Server Access

The SSH user must have:
- Read/write access to the project directory
- Permission to run `composer` and `./yii` commands
- Permission to modify `runtime/` and `web/assets/` directories

### 5. Git Configuration

Ensure the server's git repository is configured correctly:
```bash
cd /data/www/sbip
git remote -v  # Should show your GitHub repository
git config --global user.name "Deploy Bot"
git config --global user.email "deploy@example.com"
```

## Testing the Deployment

1. Make a small change to your code
2. Commit and push to `main` branch
3. Go to **Actions** tab in GitHub to see the deployment workflow
4. Check the logs to verify successful deployment

## Troubleshooting

### SSH Connection Issues
- Verify the SSH key is correctly added to GitHub secrets
- Check that the public key is in `~/.ssh/authorized_keys` on the server
- Test SSH connection manually: `ssh -i ~/.ssh/github_actions_deploy user@host`

### Permission Issues
- Ensure the SSH user has write permissions to the project directory
- Check that `composer` and `./yii` commands are executable
- Verify `runtime/` and `web/assets/` directories are writable

### Migration Issues
- Check database connection in `config/db.php`
- Verify the database user has migration permissions
- Review migration logs in `runtime/logs/`

### Cache Issues
- Manually clear cache: `./yii cache/flush-all`
- Check `runtime/cache/` directory permissions

## Security Notes

- Never commit SSH keys to the repository
- Use separate SSH keys for different environments
- Regularly rotate SSH keys
- Limit SSH key permissions on the server if possible
- Consider using SSH key passphrases for additional security

