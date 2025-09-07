# Packagist Bridge Documentation

> **Integration with Packagist API for Composer package analysis**

The Packagist Bridge communicates with the public Packagist API to retrieve metadata, download statistics and security information for Composer packages. It also falls back to GitHub for security advisories when needed.

---

## 📋 **Overview**

| Component | Purpose | Status |
|-----------|---------|--------|
| `PackagistApiClient` | Packagist API communication | ✅ Complete |
| `PackagistMetadataProvider` | Package metadata extraction | ✅ Complete |
| `PackagistActivityProvider` | Release & activity data | ✅ Complete |
| `PackagistSecurityProvider` | Security advisories via GitHub | ✅ Complete |
| `PackagistContentProvider` | Package content extraction | ✅ Complete |
| `PackagistSearchProvider` | Package search | ✅ Complete |
| `ComposerDownloadStatsProvider` | Download statistics | ✅ Complete |
| `PackagistProviderFactory` | Provider instantiation | ✅ Complete |

---

## 🔧 **Configuration**

### **Authentication**
Packagist does not require authentication for read‑only endpoints.

### **Rate Limiting**
No strict limits are published, but you should throttle high volume usage.

### **API Base**
`PackagistProviderFactory` scopes the HTTP client to `https://packagist.org/` and sets a `User-Agent` header.

---

## 🏭 **Provider Factory Usage**

```php
use PackApi\Bridge\Packagist\PackagistProviderFactory;
use PackApi\Http\HttpClientFactory;

$httpFactory = new HttpClientFactory();
$factory = new PackagistProviderFactory($httpFactory);

$metadataProvider = $factory->createMetadataProvider();
$statsProvider = $factory->createStatsProvider();
$securityProvider = $factory->createSecurityProvider();
$contentProvider = $factory->createContentProvider();
$searchProvider = $factory->createSearchProvider();
```

---

## 🔍 **API Client Methods**

### **Package Information**
```php
$client = new PackagistApiClient($httpClient);
$package = $client->fetchPackage('symfony/console');
```

### **Download Statistics**
```php
// Total, monthly and daily downloads
$range = $client->fetchPackage('symfony/console');

// Custom date range
$stats = $client->fetchDownloadsRange('symfony/console', new \DateTimeImmutable('-7 days'), new \DateTimeImmutable());
```

### **Security Advisories**
`PackagistSecurityProvider` downloads advisory YAML files from the GitHub repository `FriendsOfPHP/security-advisories`. If GitHub is unreachable or a file cannot be retrieved, an empty list is returned.

---

## 📦 **Provider Implementations**

### **Metadata Provider**
Maps fields from the `/packages/{name}.json` endpoint to the `Metadata` model.

### **Activity Provider**
Determines latest release and commit time from available versions.

### **Security Provider**
Uses GitHub API as a fallback to fetch YAML advisories for the package path under `FriendsOfPHP/security-advisories`.

### **Content Provider**
Downloads the distribution archive of the latest version using `SecureFileHandler` and analyses its files.

### **Statistics Provider**
`ComposerDownloadStatsProvider` wraps `PackagistApiClient` to expose download counts as `DownloadStats` models.

---

## 🔄 **Caching Strategy**
Packagist responses can be cached via your HTTP client. The library does not impose a specific cache layer.

### **Cache Configuration**
Enable HTTP caching at the client level in your application if desired. PackApi does not require a separate configuration class.

---

## ⚠️ **Error Handling**

### **Common Exceptions**
- Network failures throw `NetworkException`
- Malformed package identifiers throw `ValidationException`

### **HTTP Status Codes**
- **200**: Success
- **404**: Package not found → returns `null`
- **429**: Rate limit exceeded → retry later

---

## 🔧 **Advanced Configuration**

### **Custom HTTP Client Options**
```php
$httpClient = $httpFactory->createClient(['timeout' => 20]);
$factory = new PackagistProviderFactory($httpFactory);
```

### **Package URL Detection**
Only Composer package names like `vendor/package` are supported.

---

## 📊 **Performance Considerations**

### **Request Optimization**
- Cache API responses
- Avoid fetching full content archives when not required

### **Memory Usage**
- Archive extraction uses temporary disk space; ensure enough room

---

## 🧪 **Testing**
Mock HTTP responses using `MockHttpClient` to test providers without hitting Packagist or GitHub.

---

## 🔗 **Packagist API Documentation**
- **[Packagist Web API](https://packagist.org/apidoc)**

---

## 🚀 **Future Enhancements**
- More granular statistics endpoints
- Optional caching layer for downloaded archives

---

*Last updated: 2025-07-28*
