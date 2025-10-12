# Global Variables Analysis and Removal Plan

## Executive Summary

This document provides a comprehensive analysis of all global variable references in the Viavi 8800SX application and proposes a solution to eliminate them using modern PHP best practices.

## Current Global Variables

### 1. Database-Related Variables (config.php)
```php
$db_server = 'db';
$db_name = 'viavi';
$db_user = 'viavi';
$db_password = trim(file_get_contents(getenv("DB_PASSWORD_FILE")));
$link = mysqli_connect($db_server, $db_user, $db_password, $db_name);
```
- **Usage**: Direct access to `$link` in 11 files (51 occurrences)
- **Problem**: Global database connection makes testing difficult and creates tight coupling

### 2. Translation Variables (config.php)
```php
$language = 'en';
$translations = include("locales/$language.php");
```
- **Usage**: `global $translations` in `helpers.php::translate()` function
- **Problem**: Function depends on global state

### 3. File Upload Configuration (config.php)
```php
$upload_max_size = 5000000;
$upload_target_dir = "uploads/";
$upload_disallowed_exts = array(...);
```
- **Usage**: 
  - `global` declarations in `helpers.php::handleFileUpload()`
  - Direct access in alignments-create.php, alignments-delete.php, alignments-update.php
- **Problem**: Configuration scattered across global scope and direct usage

### 4. Application Configuration (config.php)
```php
$no_of_records_per_page = '10';
$appname = '8800SX';
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
$domain = $protocol . '://' . $_SERVER['HTTP_HOST'];
```
- **Usage**: Various files access these directly
- **Problem**: Global configuration state

## Impact Analysis

### Files Using Global Variables

1. **alignments-create.php** - Uses `$link`, `$upload_target_dir`
2. **alignments-delete.php** - Uses `$link`, `$upload_target_dir`
3. **alignments-update.php** - Uses `$link`, `$upload_target_dir`
4. **alignments-index.php** - Uses `$link`
5. **alignments-read.php** - Uses `$link`
6. **alignments-view.php** - Uses `$link`
7. **alignments-pdf.php** - Uses `$link`
8. **main.php** - Uses `$link`
9. **helpers.php** - Uses `global $translations`, `global $upload_*` variables
10. **error.php** - Uses `$link`
11. **upload.php** - Uses `$link`
12. **index.php** - Uses `$link`

### Functions Using Global Variables

1. **translate()** - Uses `global $translations`
2. **handleFileUpload()** - Uses `global $upload_max_size`, `$upload_target_dir`, `$upload_disallowed_exts`

## Proposed Solution: Configuration Class

### Approach

Replace global variables with a singleton Configuration class that:
1. Encapsulates all configuration settings
2. Provides getter methods for configuration values
3. Manages the database connection
4. Is easily testable and mockable

### Benefits

1. **Explicit Dependencies**: Functions declare what they need via parameters
2. **Testability**: Easy to mock configuration in tests
3. **Maintainability**: Configuration centralized in one class
4. **Type Safety**: Can add type hints and return types
5. **No Global State**: Eliminates global variable pollution

### Implementation Strategy

#### Phase 1: Create Configuration Class
Create `Config.php` class with methods:
- `getDb()` - Returns database connection
- `getTranslations()` - Returns translations array
- `getUploadConfig()` - Returns upload configuration
- `getAppConfig()` - Returns app configuration

#### Phase 2: Update Helper Functions
Modify helper functions to accept configuration as parameters:
- `translate($key, $translations, $echo = true, ...$args)`
- `handleFileUpload($FILE, $uploadConfig)`

#### Phase 3: Update All Files
Update all files to:
1. Get configuration from Config class
2. Pass configuration to helper functions
3. Use Config methods instead of global variables

#### Phase 4: Remove Global Variables
Once all files are updated, remove global variable definitions from config.php

## Detailed Removal Plan

### Step 1: Create Config Class

```php
class Config {
    private static $instance = null;
    private $dbConnection;
    private $translations;
    private $uploadConfig;
    private $appConfig;
    
    private function __construct() {
        // Initialize configuration
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }
    
    public function getDb() { return $this->dbConnection; }
    public function getTranslations() { return $this->translations; }
    public function getUploadConfig() { return $this->uploadConfig; }
    // ... more methods
}
```

### Step 2: Update helpers.php

**Before:**
```php
function translate($key, $echo = true, ...$args) {
    global $translations;
    // ... use $translations
}
```

**After:**
```php
function translate($key, $echo = true, ...$args) {
    $config = Config::getInstance();
    $translations = $config->getTranslations();
    // ... use $translations
}
```

### Step 3: Update Each File

**Before (alignments-create.php):**
```php
require_once('config.php');
// ... later in code
$stmt = $link->prepare("INSERT INTO ...");
```

**After:**
```php
require_once('config.php');
$config = Config::getInstance();
$link = $config->getDb();
// ... later in code
$stmt = $link->prepare("INSERT INTO ...");
```

## Testing Strategy

1. **Unit Tests**: Test Config class methods
2. **Integration Tests**: Test that each page still works
3. **Manual Testing**: 
   - Upload functionality
   - Translation functionality
   - Database operations
   - CRUD operations on alignments

## Migration Notes

- All changes are backwards compatible initially (both approaches work)
- Can be done incrementally, file by file
- No database changes required
- No Docker configuration changes required

## Success Criteria

- [ ] Zero uses of `global` keyword in codebase
- [ ] Zero top-level variable definitions in config.php (except Config class)
- [ ] All functionality works as before
- [ ] All tests pass
- [ ] Code is more maintainable and testable
