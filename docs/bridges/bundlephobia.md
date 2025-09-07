# BundlePhobia Bridge Documentation

> **Integration with BundlePhobia for bundle size insights**

The BundlePhobia Bridge retrieves bundle size information for NPM packages using the public BundlePhobia API.

---

## **Overview**

| Component | Purpose | Status |
|-----------|---------|--------|
| `BundlePhobiaApiClient` | HTTP client for BundlePhobia API | ✅ Complete |
| `BundlePhobiaSizeProvider` | Provides bundle size metrics | ✅ Complete |
| `BundlePhobiaProviderFactory` | Creates the size provider and API client | ✅ Complete |

---

## **Provider Factory Usage**

```php
use PackApi\Bridge\BundlePhobia\BundlePhobiaProviderFactory;
use PackApi\Http\SymfonyHttpClientFactory;

$httpFactory = new SymfonyHttpClientFactory();
$httpClient = $httpFactory->createClient();

$factory = new BundlePhobiaProviderFactory($httpClient);

// Available provider interfaces
$interfaces = $factory->provides();
// Returns: [BundleSizeProviderInterface::class]

// Create the bundle size provider
$sizeProvider = $factory->createBundleSizeProvider();
```

---

## **API Client Methods**

### **Bundle Size**

```php
use PackApi\Bridge\BundlePhobia\BundlePhobiaApiClient;

$client = $factory->getApiClient();

// Latest version
$size = $client->getBundleSize('react');

// Specific version
$size = $client->getBundleSize('react', '17.0.2');
```

### **Package History**

```php
$history = $client->getPackageHistory('react');
// Returns array with versions and size information
```

---

## **Provider Methods**

The `BundlePhobiaSizeProvider` wraps the API client with package objects:

```php
use PackApi\Package\NpmPackage;

$package = new NpmPackage('react');
$bundleSize = $sizeProvider->getBundleSize($package);
$bundleSizeVersion = $sizeProvider->getBundleSizeForVersion($package, '17.0.2');
$packageHistory = $sizeProvider->getPackageHistory($package);
```
