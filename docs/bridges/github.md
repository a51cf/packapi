# GitHub Bridge Documentation

> **Integration with GitHub API for repository analysis and activity tracking**

The GitHub Bridge provides comprehensive integration with GitHub's REST API to analyze repositories, track activity, and gather security information.

---

## 📋 **Overview**

| Component | Purpose | Status |
|-----------|---------|--------|
| `GitHubApiClient` | GitHub API communication | ✅ Complete |
| `GitHubMetadataProvider` | Repository metadata | ✅ Complete |
| `GitHubActivityProvider` | Commit activity & releases | ✅ Complete |
| `GitHubSecurityProvider` | Security advisories | ✅ Complete |
| `GitHubContentProvider` | Repository file analysis | ✅ Complete |
| `GitHubSearchProvider` | Repository search | ✅ Complete |
| `GitHubStatisticProvider` | Repository statistics | ✅ Complete |
| `GitHubProviderFactory` | Provider instantiation | ✅ Complete |

---

## 🔧 **Configuration**

### **Authentication**
GitHub API supports authentication for higher rate limits and private repositories. Provide a token via environment and pass it to the factory:

```php
$_ENV['GITHUB_TOKEN'] = 'ghp_your_token_here';
```

### **Rate Limiting**
- **Authenticated**: 5,000 requests/hour
- **Unauthenticated**: 60 requests/hour

```php
$factory = new GitHubProviderFactory($httpClient, $config);
// Automatic rate limiting with TokenBucketRateLimiter
```

---

## 🏭 **Provider Factory Usage**

```php
use PackApi\Bridge\GitHub\GitHubProviderFactory;
use PackApi\Http\HttpClientFactory;

$httpFactory = new HttpClientFactory();
$factory = new GitHubProviderFactory($httpFactory, $_ENV['GITHUB_TOKEN'] ?? null);

$metadataProvider = $factory->createMetadataProvider();
$activityProvider = $factory->createActivityProvider();
```

---

## 🔍 **API Client Methods**

### **Repository Information**
```php
use PackApi\Bridge\GitHub\GitHubApiClient;

$httpClient = (new HttpClientFactory())->createClient()->withOptions([
    'base_uri' => 'https://api.github.com/',
    'headers' => [
        'Authorization' => $_ENV['GITHUB_TOKEN'] ? 'Bearer ' . $_ENV['GITHUB_TOKEN'] : '',
        'Accept' => 'application/vnd.github.v3+json',
        'User-Agent' => 'PackApi/1.0',
    ],
]);

$client = new GitHubApiClient($httpClient);

// Example: repository metadata
$repo = $client->fetchRepoMetadata('owner/repo-name');

// Example: repository contents
$content = $client->fetchRepoContents('owner/repo-name', 'path/to/file');
```

### **Activity & Releases**
```php
// Get recent releases
$releases = $client->fetchReleases('owner', 'repo-name');
// Returns: array of release data with tags, dates, assets

// Get commit activity
$activity = $client->fetchCommitActivity('owner', 'repo-name');
// Returns: commit statistics over time
```

### **Security Advisories**
```php
// Get security advisories
$advisories = $client->fetchSecurityAdvisories('owner', 'repo-name');
// Returns: array of security vulnerability data
```

---

## 📦 **Provider Implementations**

### **Metadata Provider**
Extracts repository metadata and basic information:

```php
use PackApi\Bridge\GitHub\GitHubMetadataProvider;
use PackApi\Package\ComposerPackage;

$provider = new GitHubMetadataProvider($apiClient);
$package = new ComposerPackage('symfony/console');

if ($provider->supports($package)) {
    $metadata = $provider->getMetadata($package);
    
    echo $metadata->name;        // symfony/console
    echo $metadata->description; // Repository description
    echo $metadata->license;     // MIT
    echo $metadata->repository;  // https://github.com/symfony/console
}
```

**Supported Package Types**: All packages with repository URLs

### **Activity Provider**
Analyzes repository activity and maintenance:

```php
use PackApi\Bridge\GitHub\GitHubActivityProvider;

$provider = new GitHubActivityProvider($apiClient);
$activity = $provider->getActivity($package);

echo $activity->lastCommitDate;    // DateTimeImmutable
echo $activity->totalCommits;      // int
echo $activity->activeContributors; // int
echo $activity->averageTimeToClose; // Time in days
```

### **Security Provider**
Fetches security advisories and vulnerabilities:

```php
use PackApi\Bridge\GitHub\GitHubSecurityProvider;

$provider = new GitHubSecurityProvider($apiClient);
$advisories = $provider->getSecurityAdvisories($package);

foreach ($advisories as $advisory) {
    echo $advisory->severity;     // HIGH, MEDIUM, LOW
    echo $advisory->summary;      // Vulnerability description
    echo $advisory->publishedAt;  // DateTimeImmutable
}
```

### **Content Provider**
Analyzes repository file structure:

```php
use PackApi\Bridge\GitHub\GitHubContentProvider;

$provider = new GitHubContentProvider($apiClient);
$content = $provider->getContentOverview($package);

echo $content->fileCount;      // Total files
echo $content->hasReadme();    // bool
echo $content->hasLicense();   // bool
echo $content->hasTests();     // bool
```

---

## 🔄 **Caching Strategy**

The GitHub Bridge implements intelligent caching:

```php
// Cache keys format
'github_api_repo_' . md5($owner . '/' . $repo)
'github_api_releases_' . md5($owner . '/' . $repo)
'github_api_security_' . md5($owner . '/' . $repo)

// Default TTL: 1 hour for metadata, 15 minutes for activity
```

### **Cache Configuration**
If you want HTTP caching, wrap the client with Symfony's `CachingHttpClient` via your own `Store` implementation and pass it through `HttpClientFactory` in your app. PackApi does not expose a separate configuration class.

---

## ⚠️ **Error Handling**

### **Common Exceptions**
```php
try {
    $metadata = $provider->getMetadata($package);
} catch (NetworkException $e) {
    // Network connectivity issues
    error_log('GitHub API unreachable: ' . $e->getMessage());
} catch (RateLimitException $e) {
    // Rate limit exceeded
    error_log('GitHub rate limit hit: ' . $e->getMessage());
} catch (ValidationException $e) {
    // Invalid repository URL or package
    error_log('Invalid package: ' . $e->getMessage());
}
```

### **HTTP Status Codes**
- **200**: Success
- **404**: Repository not found → returns `null`
- **403**: Rate limit exceeded → throws `RateLimitException`
- **401**: Authentication failed → throws `ApiException`

---

## 🔧 **Advanced Configuration**

### **Custom HTTP Client Options**
```php
$httpClient = $httpFactory->createClient([
    'timeout' => 30,
    'max_redirects' => 3,
    'headers' => [
        'User-Agent' => 'PackApi/1.0',
        'Accept' => 'application/vnd.github.v3+json'
    ]
]);
```

### **Repository URL Detection**
The GitHub Bridge automatically detects repository URLs from:
- Package composer.json `repository` field
- Package package.json `repository` field  
- Direct GitHub URLs in package metadata

```php
// Supported URL formats
https://github.com/owner/repo
git@github.com:owner/repo.git
https://github.com/owner/repo.git
```

---

## 📊 **Performance Considerations**

### **Request Optimization**
- Batch requests where possible
- Use conditional requests with ETags
- Implement exponential backoff for rate limits
- Cache responses aggressively

### **Memory Usage**
- Stream large file downloads
- Paginate through large result sets
- Use generators for processing

---

## 🧪 **Testing**

Use Symfony's `MockHttpClient` to simulate responses in unit tests, or mock provider classes directly. Keep tests focused on typed models and provider behavior.

---

## 🔗 **GitHub API Documentation**

- **[GitHub REST API](https://docs.github.com/en/rest)**
- **[Authentication](https://docs.github.com/en/rest/guides/getting-started-with-the-rest-api)**
- **[Rate Limiting](https://docs.github.com/en/rest/overview/resources-in-the-rest-api#rate-limiting)**
- **[Security Advisories](https://docs.github.com/en/rest/security-advisories)**

---

## 🚀 **Future Enhancements**

- **GraphQL API Integration** - More efficient data fetching
- **GitHub Apps Support** - Higher rate limits
- **Webhook Integration** - Real-time updates
- **Enterprise GitHub Support** - Custom API endpoints

---

*Last updated: 2025-07-28*
