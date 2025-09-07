# PackApi Documentation

> **Modern PHP Library for Package Analysis Across Multiple Ecosystems**

Welcome to the comprehensive documentation for PackApi, a provider-based library that analyzes open source packages from Composer, NPM, GitHub, jsDelivr, and more.

---

## 📚 **Documentation Structure**

### **Getting Started**
- **[Installation & Setup](guides/installation.md)** - Quick start guide
- **[Basic Usage](guides/basic-usage.md)** - Your first package analysis
- **[Configuration](guides/configuration.md)** - Settings and customization

### **Bridge Integrations** 📦
External service integrations for fetching package data:

- **[GitHub Bridge](bridges/github.md)** - Repository analysis and activity tracking
- **[Packagist Bridge](bridges/packagist.md)** - Composer package metadata and stats
- **[NPM Bridge](bridges/npm.md)** - Node.js package registry integration
- **[jsDelivr Bridge](bridges/jsdelivr.md)** - CDN statistics and content analysis
- **[BundlePhobia Bridge](bridges/bundlephobia.md)** - Bundle size analysis service
- **[OSV Bridge](bridges/osv.md)** - Security advisories from OSV

### **Package Systems** 🔧
Package-specific implementations and providers:

- **[Composer System](systems/composer.md)** - PHP package ecosystem
- **[NPM System](systems/npm.md)** - Node.js package ecosystem

### **Analysis Types** 🔍
Available package analysis capabilities:

- **[Metadata Analysis](analysis/metadata.md)** - Package information, licensing, and repository details
- **[Download Statistics](analysis/download-stats.md)** - Usage metrics and trends
- **[Content Analysis](analysis/content.md)** - File structure, documentation, and code quality
- **[Security Analysis](analysis/security.md)** - Vulnerability scanning and advisories
- **[Activity Analysis](analysis/activity.md)** - Repository activity and maintenance status
- **[Quality Analysis](analysis/quality.md)** - Code quality scoring and best practices

### **API Reference** 📖
- **[Core Classes](api/core.md)** - Main interfaces and facades
- **[Providers](api/providers.md)** - Provider interfaces and implementations
- **[Inspectors](api/inspectors.md)** - Analysis orchestration classes
- **[Models](api/models.md)** - Data objects and structures
- **[Exceptions](api/exceptions.md)** - Error handling classes

---

## 🚀 **Quick Start Example**

```php
<?php

use PackApi\Builder\PackApiBuilder;
use PackApi\Package\ComposerPackage;

$facade = (new PackApiBuilder())
    ->withGitHubToken($_ENV['GITHUB_TOKEN'] ?? null)
    ->build();

$result = $facade->analyze(new ComposerPackage('symfony/ux-icons'));

$metadata = $result['metadata'];
$downloads = $result['downloads'];

echo "Package: {$metadata->getName()}\n";
echo "Description: {$metadata->getDescription()}\n";
echo "Downloads: " . ($downloads->get('monthly')?->getCount() ?? 'N/A') . "\n";
```

---

## 🏗️ **Architecture Overview**

PackApi follows a **provider-based architecture** with three key layers:

```
┌─────────────────┐
│     Facade      │ ← Single entry point
├─────────────────┤
│   Inspectors    │ ← Orchestration layer
├─────────────────┤
│   Providers     │ ← Data source implementations
└─────────────────┘
```

### **Key Patterns**
- **Provider Pattern**: Each data source (GitHub, NPM, etc.) implements provider interfaces
- **Inspector Pattern**: Orchestrates multiple providers for comprehensive analysis
- **Bridge Pattern**: Encapsulates external API integrations
- **Factory Pattern**: Creates configured provider instances

---

## 🔧 **Supported Ecosystems**

| Ecosystem | Package Type | Metadata | Downloads | Content | Security | Activity |
|-----------|--------------|----------|-----------|---------|----------|----------|
| **Composer** | `ComposerPackage` | ✅ | ✅ | ✅ | ✅ | ✅ |
| **NPM** | `NpmPackage` | ✅ | ✅ | ✅ | ❌ | ❌ |
| **GitHub** | Any | ✅ | ❌ | ✅ | ✅ | ✅ |
| **jsDelivr** | Any | ✅ | ✅ | ✅ | ❌ | ❌ |

---

## 📋 **Requirements**

- **PHP**: 8.3 or higher
- **Extensions**: `curl`, `json`, `mbstring`
- **Dependencies**: Symfony HTTP Client, PSR-compatible logger

---

## 🤝 **Contributing**

1. Fork the repository and create a feature branch
2. Write tests for your changes and ensure they pass
3. Follow PSR-12 code standards with `composer cs-fix`
4. Submit a pull request with a clear description

---

## 📄 **License**

This project is licensed under the MIT License. See the [LICENSE](../LICENSE) file for details.

---

## 🔗 **Links**

- **[GitHub Repository](https://github.com/smnandre/packapi)**
- **[Issue Tracker](https://github.com/smnandre/packapi/issues)**
- **[Changelog](../CHANGELOG.md)**

---

*Documentation last updated: 2025-07-28*