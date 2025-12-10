# Coding Standards and Code Quality Tools

This document describes the coding standards and automated tools used in this project to enforce code quality and catch common issues, especially AI-generated code problems.

## Quick Reference

| Tool | Purpose | Auto-fixes? | What It Catches |
|------|---------|-------------|-----------------|
| **PHP CS Fixer** | PHP formatting | ✅ Yes | Spacing, indentation, braces, quotes, array syntax, unused imports |
| **PHP_CodeSniffer** | PHP code quality & safety | ❌ No | Unused variables, deprecated functions, security issues, best practices |
| **PHPStan** | PHP static analysis | ❌ No | Fake method calls, wrong return types, null errors, type mismatches |
| **ESLint** | JavaScript formatting & quality | ✅ Yes | Spacing, quotes, unused variables, console.log, semicolons |
| **TypeScript** | JavaScript type checking | ❌ No | Fake properties, missing await, broken promises, type mismatches |


## Configuration Files

All in project root:
- `.php-cs-fixer.php` → **PHP CS Fixer** (PHP formatting)
- `phpcs.xml` → **PHP_CodeSniffer** (PHP code quality)
- `phpstan.neon` → **PHPStan** (PHP static analysis)
- `.eslintrc.js` → **ESLint** (JavaScript formatting/quality)
- `tsconfig.json` → **TypeScript** (JavaScript type checking)
- `.editorconfig` → **EditorConfig** (Editor settings)
- `package.json` → **NPM** (Scripts and dependencies)

## Setup (only once)

```bash
# Install PHP tools
composer install

# Install JavaScript tools
npm install

# Initialize git hooks
npm run prepare
```

Update `.gitignore`:
```
.php-cs-fixer.cache
.eslintcache
phpstan-baseline.neon
node_modules/
.husky/_/
```

## Usage

### PHP

```bash
# Formatting (auto-fixes)
npm run lint:php          # Check
npm run lint:php:fix       # Fix

# Code quality (detection only)
npm run lint:phpcs

# Static analysis (detection only)
npm run lint:phpstan
```

### JavaScript

```bash
# Formatting & quality (auto-fixes)
npm run lint:js            # Check
npm run lint:js:fix        # Fix

# Type checking (detection only)
npm run lint:js:types
```

### All Tools

```bash
npm run lint:all        # Check entire codebase (use sparingly)
npm run lint:changed    # Check all changed files (staged + unstaged since last commit)
```

**Recommended workflow:**
- Pre-commit hooks run automatically on staged files
- Use `lint:changed` to check what you've modified (before or after staging)
- Use `lint:all` only when you want to check the entire codebase

## Pre-commit Hooks vs CI/CD

ESLint, PHP CS Fixer, and PHP_CodeSniffer run in pre-commit hook.

All other tools run in CI pipeline for the entire codebase.

### Manual Checks

**For local development:**
```bash
npm run lint:changed    # Check only files you've modified
npm run lint:all        # Check entire codebase (use before pushing)
```

**Note:** Pre-commit hooks only check staged files, so existing code is left as-is. This allows gradual adoption while CI ensures full codebase quality.

## IDE Integration

### PHPStorm/IntelliJ
- PHP CS Fixer: Settings → Tools → PHP CS Fixer
- PHP_CodeSniffer: Settings → PHP → Quality Tools → PHP_CodeSniffer
- PHPStan: Install plugin, Settings → PHP → Quality Tools → PHPStan
- ESLint: Install plugin, Settings → JavaScript → Code Quality Tools → ESLint
- TypeScript: Settings → TypeScript → Enable for JavaScript files too

### VS Code
Install extensions: PHP CS Fixer, PHP_CodeSniffer, PHPStan, ESLint, TypeScript (built-in), EditorConfig

## Troubleshooting

- **PHP CS Fixer cache**: `rm .php-cs-fixer.cache`
- **Pre-commit hooks not running**: `npm install && npm run prepare && chmod +x .husky/pre-commit`
- **Many TypeScript errors**: Add JSDoc, use `@ts-ignore` for legacy code, or adjust strictness
- **Conflicts with existing code**: Use gradual adoption, whitelist problematic files, fix incrementally

## Resources

- [PHP CS Fixer](https://cs.symfony.com/)
- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [PHPStan](https://phpstan.org/)
- [ESLint](https://eslint.org/)
- [TypeScript](https://www.typescriptlang.org/)
- [EditorConfig](https://editorconfig.org/)
