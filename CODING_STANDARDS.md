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

# Static analysis - AI detector (detection only)
npm run lint:phpstan
```

### JavaScript

```bash
# Formatting & quality (auto-fixes)
npm run lint:js            # Check
npm run lint:js:fix        # Fix

# Type checking - AI detector (detection only)
npm run lint:js:types
```

### All Tools

```bash
npm run lint:all
```

## Pre-commit Hooks

Automatically runs:
- ESLint (auto-fix) on JavaScript files
- PHP CS Fixer (auto-fix) on PHP files

**Note:** TypeScript checking is not in pre-commit (can be slow). Runs on CI only.

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
