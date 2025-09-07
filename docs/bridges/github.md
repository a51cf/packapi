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
GitHub API requires authentication for higher rate limits and private repositories:

```php
// Environment variable (recommended)
$_ENV['GITHUB_TOKEN'] = 'ghp_your_token_here';

// Or configure directly
$config = new Configuration([
    'github' => [
        'token' => 'ghp_your_token_here'
    ]
]);
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
use PackApi\Config\Configuration;

$httpFactory = new HttpClientFactory();
$httpClient = $httpFactory->createClient();
$config = new Configuration();

$factory = new GitHubProviderFactory($httpClient, $config);

// Get available provider types
$providers = $factory->provides(); 
// Returns: [MetadataProviderInterface::class, ActivityProviderInterface::class, ...]

// Create specific providers
$metadataProvider = $factory->create(MetadataProviderInterface::class);
$activityProvider = $factory->create(ActivityProviderInterface::class);
```

---

## 🔍 **API Client Methods**

### **Repository Information**
```php
$client = new GitHubApiClient($httpClient, $cache, $rateLimiter, $logger);

// Get repository metadata
$repoData = $client->fetchRepository('owner', 'repo-name');
// Returns: array with name, description, stars, forks, etc.

// Get repository content
$content = $client->fetchRepositoryContent('owner', 'repo-name', 'path/to/file');
// Returns: file content and metadata
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
```php
$config = new Configuration([
    'cache' => [
        'type' => 'filesystem',
        'directory' => '/path/to/cache'
    ]
]);
```

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

```php
// Mock GitHub API client for testing
$mockClient = $this->createMock(GitHubApiClient::class);
$mockClient->method('fetchRepository')
           ->willReturn(['name' => 'test-repo', 'description' => 'Test']);

$provider = new GitHubMetadataProvider($mockClient);
$metadata = $provider->getMetadata($package);

$this->assertSame('test-repo', $metadata->name);
```

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
