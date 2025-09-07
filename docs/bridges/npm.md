# NPM Bridge Documentation

> **Integration with NPM Registry API for Node.js package analysis**

The NPM Bridge provides comprehensive integration with the NPM Registry to analyze Node.js packages, gather metadata, download statistics, and content information.

---

## **Overview**

| Component | Purpose | Status |
|-----------|---------|--------|
| `NpmApiClient` | NPM Registry API communication | ✅ Complete |
| `NpmMetadataProvider` | Package metadata extraction | ✅ Complete |
| `NpmDownloadStatsProvider` | Download statistics | ✅ Complete |
| `NpmContentProvider` | Package content analysis | ✅ Complete |
| `NpmProviderFactory` | Provider instantiation | ✅ Complete |

---

## **Configuration**

### **API Endpoints**
- **Registry**: `https://registry.npmjs.org`
- **Statistics**: `https://api.npmjs.org`

### **Rate Limiting**
- **No official limits** but respectful usage recommended
- Built-in rate limiting with `TokenBucketRateLimiter`

```php
use PackApi\Bridge\Npm\NpmProviderFactory;
use PackApi\Config\Configuration;

$config = new Configuration([
    'cache' => [
        'type' => 'filesystem',
        'directory' => '/tmp/packapi-cache'
    ],
    'logger' => [
        'file' => '/var/log/packapi.log'
    ]
]);

$factory = new NpmProviderFactory($httpClient, $config);
```

---

## **Provider Factory Usage**

```php
use PackApi\Bridge\Npm\NpmProviderFactory;
use PackApi\Http\HttpClientFactory;

$httpFactory = new HttpClientFactory();
$httpClient = $httpFactory->createClient(['enable_quic' => true]);

$factory = new NpmProviderFactory($httpClient, $config);

// Available provider interfaces
$providers = $factory->provides();
// Returns: [
//     MetadataProviderInterface::class,
//     DownloadStatsProviderInterface::class,
//     ContentProviderInterface::class
// ]

// Create providers
$metadataProvider = $factory->createMetadataProvider();
$statsProvider = $factory->createDownloadStatsProvider();
$contentProvider = $factory->createContentProvider();
```

---

## **API Client Methods**

### **Package Information**
```php
use PackApi\Bridge\Npm\NpmApiClient;

$client = new NpmApiClient($httpClient, $cache, $rateLimiter, $logger);

// Fetch package metadata
$packageData = $client->fetchPackageInfo('lodash');
// Returns: Complete NPM registry data including versions, dependencies, etc.

$packageData = $client->fetchPackageInfo('@angular/core');
// Supports scoped packages

// Returns null if package not found
$notFound = $client->fetchPackageInfo('non-existent-package'); // null
```

### **Download Statistics**
```php
// Fetch download stats for different periods
$stats = $client->fetchDownloadStats('lodash', 'last-month');
$stats = $client->fetchDownloadStats('lodash', 'last-week');
$stats = $client->fetchDownloadStats('lodash', 'last-day');

// Custom date ranges
$stats = $client->fetchDownloadStats('lodash', '2023-01-01:2023-12-31');
```

---

## **Provider Implementations**

### **Metadata Provider**
Extracts comprehensive package metadata with robust error handling:

```php
use PackApi\System\Npm\NpmMetadataProvider;
use PackApi\Package\NpmPackage;

$provider = new NpmMetadataProvider($apiClient);
$package = new NpmPackage('lodash');

if ($provider->supports($package)) {
    $metadata = $provider->getMetadata($package);
    
    echo $metadata->name;        // lodash
    echo $metadata->description; // A modern JavaScript utility library
    echo $metadata->license;     // MIT
    echo $metadata->repository;  // https://github.com/lodash/lodash.git
}
```

**Features**:
- ✅ Handles missing fields gracefully
- ✅ Supports both string and object license formats
- ✅ Extracts repository URL from various formats
- ✅ Falls back to package name if registry name missing

**Supported License Formats**:
```javascript
// String format
"license": "MIT"

// Object format  
"license": {
  "type": "MIT",
  "url": "https://opensource.org/licenses/MIT"
}
```

**Repository URL Extraction**:
```javascript
// String format
"repository": "https://github.com/user/repo.git"

// Object format
"repository": {
  "type": "git",
  "url": "https://github.com/user/repo.git"
}
```

### **Download Stats Provider**
Provides download statistics from NPM's API:

```php
use PackApi\System\Npm\NpmDownloadStatsProvider;
use PackApi\Model\DownloadPeriod;

$provider = new NpmDownloadStatsProvider($apiClient);
$stats = $provider->getStats($package); // Default: last month

// Specific periods
$monthlyPeriod = new DownloadPeriod('monthly', 0, new \DateTimeImmutable('-1 month'), new \DateTimeImmutable());
$weeklyPeriod = new DownloadPeriod('weekly', 0, new \DateTimeImmutable('-1 week'), new \DateTimeImmutable());
$dailyPeriod = new DownloadPeriod('daily', 0, new \DateTimeImmutable('-1 day'), new \DateTimeImmutable());

$monthlyStats = $provider->getStatsForPeriod($package, $monthlyPeriod);
$weeklyStats = $provider->getStatsForPeriod($package, $weeklyPeriod);
$dailyStats = $provider->getStatsForPeriod($package, $dailyPeriod);

echo $stats->getCdnRequests(); // CDN request count (if available)
```

### **Content Provider** 
Analyzes package content and structure:

```php
use PackApi\System\Npm\NpmContentProvider;

$provider = new NpmContentProvider($apiClient);
$content = $provider->getContentOverview($package);

echo $content->fileCount;      // Number of files in package
echo $content->totalSize;      // Unpacked size in bytes
echo $content->hasReadme();    // Boolean: has README
echo $content->hasLicense();   // Boolean: has license
echo $content->hasTests();     // Boolean: has test framework
```

**Test Framework Detection**:
The provider detects tests from:
- **Scripts**: `test`, `jest`, `mocha`, `jasmine`, `karma`
- **Dev Dependencies**: `jest`, `mocha`, `ava`, `tape`, `tap`, etc.

```javascript
// Detected from scripts
{
  "scripts": {
    "test": "jest",
    "test:watch": "jest --watch"
  }
}

// Detected from devDependencies
{
  "devDependencies": {
    "jest": "^29.0.0",
    "mocha": "^10.0.0"
  }
}
```

---

## 🔧 **Package Validation**

NPM packages undergo strict validation:

```php
use PackApi\Package\NpmPackage;
use PackApi\Exception\ValidationException;

try {
    // Valid packages
    $pkg1 = new NpmPackage('lodash');
    $pkg2 = new NpmPackage('@angular/core');
    $pkg3 = new NpmPackage('my-package.name');
    $pkg4 = new NpmPackage('package_with_underscores');
    
} catch (ValidationException $e) {
    echo $e->getMessage();
}
```

**Validation Rules**:
- ✅ Length: 1-214 characters
- ✅ Lowercase letters only
- ✅ Numbers, hyphens, underscores, dots allowed
- ✅ Scoped packages: `@scope/package-name`
- ❌ No spaces or uppercase letters
- ❌ Cannot start/end with dots or hyphens
- ❌ No consecutive dots (`..`)

**Invalid Examples**:
```php
new NpmPackage('MyPackage');        // Uppercase
new NpmPackage('my package');       // Spaces
new NpmPackage('.package');         // Starts with dot
new NpmPackage('package-');         // Ends with hyphen
new NpmPackage('pack..age');        // Consecutive dots
new NpmPackage('@Invalid/Package'); // Invalid scoped format
```

---

## **Caching Strategy**

Intelligent caching with configurable backends:

```php
// Cache keys
'npm_api_package_' . md5($packageName)
'npm_api_stats_' . md5($packageName . $period)

// Default TTL: 1 hour for metadata, 15 minutes for stats
```

**Cache Backends**:
```php
// Filesystem cache
$config = new Configuration([
    'cache' => [
        'type' => 'filesystem',
        'directory' => '/var/cache/packapi'
    ]
]);

// Memory cache (default)
$config = new Configuration([
    'cache' => ['type' => 'memory']
]);
```

---

##  **Error Handling**

### **Network Errors**
```php
use PackApi\Exception\NetworkException;
use PackApi\Exception\RateLimitException;

try {
    $metadata = $provider->getMetadata($package);
} catch (NetworkException $e) {
    // Network connectivity issues
    error_log('NPM Registry unreachable: ' . $e->getMessage());
} catch (RateLimitException $e) {
    // Rate limit exceeded (rare with NPM)
    error_log('NPM rate limit exceeded: ' . $e->getMessage());
}
```

### **HTTP Status Handling**
- **200**: Success
- **404**: Package not found → returns `null`
- **5xx**: Server errors → throws `NetworkException`

### **Package Not Found**
```php
$metadata = $provider->getMetadata(new NpmPackage('non-existent-package'));
// Returns null, does not throw exception

if ($metadata === null) {
    echo "Package not found on NPM registry";
}
```

---

## **Performance Optimization**

### **HTTP/3 Support**
```php
$httpClient = $httpFactory->createClient([
    'enable_quic' => true,  // Enable HTTP/3 for faster requests
    'timeout' => 30,
    'max_redirects' => 3
]);
```

### **Batch Processing**
```php
// Process multiple packages efficiently
$packages = ['lodash', 'axios', 'express', 'react'];
$results = [];

foreach ($packages as $packageName) {
    $package = new NpmPackage($packageName);
    $results[$packageName] = [
        'metadata' => $metadataProvider->getMetadata($package),
        'stats' => $statsProvider->getStats($package),
        'content' => $contentProvider->getContentOverview($package)
    ];
}
```

---

## **Testing**

Comprehensive test coverage with mocked API responses:

```php
use PackApi\Tests\System\Npm\NpmMetadataProviderTest;

class NpmMetadataProviderTest extends TestCase
{
    public function testHandlesMissingFields(): void
    {
        $mockClient = $this->createMock(NpmApiClient::class);
        $mockClient->method('fetchPackageInfo')
                   ->willReturn(['name' => 'test-package']);
        
        $provider = new NpmMetadataProvider($mockClient);
        $metadata = $provider->getMetadata(new NpmPackage('test-package'));
        
        $this->assertSame('test-package', $metadata->name);
        $this->assertNull($metadata->description);
        $this->assertNull($metadata->license);
    }
}
```

**Test Coverage**:
- ✅ Package validation (11 test cases)
- ✅ Metadata extraction (8 test cases)
- ✅ Content analysis (10 test cases)
- ✅ Download stats (pending implementation)

---

## **NPM Registry Documentation**

- **[NPM Registry API](https://github.com/npm/registry/blob/master/docs/REGISTRY-API.md)**
- **[Download Statistics API](https://github.com/npm/download-counts)**
- **[Package JSON Specification](https://docs.npmjs.com/cli/v8/configuring-npm/package-json)**

---

## **Future Enhancements**

- **Tarball Analysis** - Deep file content inspection
- **Dependency Tree** - Analyze package dependencies
- **Version History** - Track package evolution
- **Vulnerability Scanning** - Integration with npm audit
- **Bundle Size Analysis** - Package size impact

---

## **Usage Examples**

### **Package Comparison**
```php
$packages = ['lodash', 'underscore', 'ramda'];
$comparison = [];

foreach ($packages as $name) {
    $pkg = new NpmPackage($name);
    $metadata = $metadataProvider->getMetadata($pkg);
    $stats = $statsProvider->getStats($pkg);
    
    $comparison[$name] = [
        'description' => $metadata->description,
        'license' => $metadata->license,
        'downloads' => $stats->getTotal(),
        'size' => $contentProvider->getContentOverview($pkg)->totalSize
    ];
}

print_r($comparison);
```

### **Popular Package Analysis**
```php
$popularPackages = ['react', 'vue', 'angular', 'svelte'];

foreach ($popularPackages as $name) {
    $pkg = new NpmPackage($name);
    $content = $contentProvider->getContentOverview($pkg);
    
    echo sprintf(
        "%s: %d files, %s, Tests: %s\n",
        $name,
        $content->fileCount,
        $this->formatBytes($content->totalSize),
        $content->hasTests() ? 'Yes' : 'No'
    );
}
```
