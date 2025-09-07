# OSV Bridge Documentation


> **Integration with the Open Source Vulnerabilities (OSV) API for vulnerability scanning**

The OSV Bridge connects PackApi to [OSV.dev](https://osv.dev/) allowing security providers to retrieve vulnerability advisories for a wide range of ecosystems.

---

## 📋 **Overview**

| Component | Purpose | Status |
|-----------|---------|--------|
| `OSVApiClient` | OSV API communication | ✅ Complete |
| `OSVSecurityProvider` | Security advisories | ✅ Complete |
| `OSVProviderFactory` | Provider instantiation | ✅ Complete |

---

## 🔧 **Configuration**

- **API Base URI**: `https://api.osv.dev/` (set in `OSVProviderFactory`)
- **Authentication**: none required
- **Default Timeout**: 30 seconds

```php
use PackApi\\Bridge\\OSV\\OSVProviderFactory;
use PackApi\\Http\\HttpClientFactory;

$httpFactory = new HttpClientFactory();
$factory = new OSVProviderFactory($httpFactory);
```

---

## 🏭 **Provider Factory Usage**

```php
use PackApi\Bridge\OSV\OSVProviderFactory;
use PackApi\Provider\SecurityProviderInterface;

$factory = new OSVProviderFactory($httpFactory);

// Available provider interfaces
$providers = $factory->provides();
// Returns: [SecurityProviderInterface::class]

// Create the security provider
$securityProvider = $factory->create(SecurityProviderInterface::class);
```

---

## 🔍 **API Client Methods**

```php
use PackApi\Bridge\OSV\OSVApiClient;

$client = $factory->getApiClient();

// Query vulnerabilities for a package
$result = $client->queryVulnerabilities('npm', 'lodash');

// Query for a specific version
$result = $client->queryVulnerabilities('Packagist', 'symfony/console', '6.3.0');

// Get vulnerability details by ID
$details = $client->getVulnerabilityById('OSV-2023-1234');
```

---

## 📦 **Provider Implementations**

### **OSV Security Provider**

The security provider supports both **Composer** and **NPM** packages. It uses `OSVApiClient` to retrieve advisories and extracts severity from CVSS scores or `database_specific` fields.

```php
use PackApi\Bridge\OSV\OSVSecurityProvider;
use PackApi\Package\ComposerPackage;

$provider = new OSVSecurityProvider($client);
$package = new ComposerPackage('symfony/console');

$advisories = $provider->getSecurityAdvisories($package);

foreach ($advisories as $advisory) {
    echo $advisory->getId();
    echo $advisory->getSeverity();
}
```

---

*Last updated: 2025-07-28*
