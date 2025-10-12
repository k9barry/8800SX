# Future Migration Guide - Complete Global Variable Removal

## Current Status

✅ **COMPLETE**: All `global` keyword usage has been eliminated from helper functions.

The current implementation successfully removes all global variable references from functions using the `global` keyword. However, the `config.php` file still exports variables for backward compatibility with existing code:

```php
// config.php - Current implementation
$link = $config->getDb();
$upload_target_dir = $config->getUploadTargetDir();
$no_of_records_per_page = $config->getNoOfRecordsPerPage();
// ... etc
```

These exported variables are **NOT required** for the primary goal (removing global keyword usage) but could be eliminated in a future enhancement if desired.

## Optional Future Enhancement

If you want to take the refactoring further and eliminate these exported variables completely, follow this migration guide.

### Phase 1: Update Individual Files (Incremental Migration)

You can migrate files one at a time. For each file that uses configuration:

#### Example: alignments-create.php

**Before:**
```php
<?php
require_once('config.php');
require_once('helpers.php');

// ... later in code
$stmt = $link->prepare("INSERT INTO ...");
// or
unlink($upload_target_dir . $result['success']);
```

**After:**
```php
<?php
require_once('Config.php');
require_once('helpers.php');

// Get configuration instance
$config = Config::getInstance();
$link = $config->getDb();
$upload_target_dir = $config->getUploadTargetDir();

// ... later in code
$stmt = $link->prepare("INSERT INTO ...");
// or
unlink($upload_target_dir . $result['success']);
```

Or even better, retrieve values as needed:
```php
<?php
require_once('Config.php');
require_once('helpers.php');

$config = Config::getInstance();

// ... later in code
$stmt = $config->getDb()->prepare("INSERT INTO ...");
// or
unlink($config->getUploadTargetDir() . $result['success']);
```

### Phase 2: Files to Migrate

Here's a checklist of files that currently rely on the exported variables from config.php:

#### Files Using `$link` (Database Connection)
- [ ] alignments-create.php
- [ ] alignments-delete.php
- [ ] alignments-index.php
- [ ] alignments-pdf.php
- [ ] alignments-read.php
- [ ] alignments-update.php
- [ ] alignments-view.php
- [ ] main.php
- [ ] error.php
- [ ] upload.php
- [ ] index.php

#### Files Using `$upload_target_dir`
- [ ] alignments-create.php
- [ ] alignments-delete.php
- [ ] alignments-update.php

#### Files Using Other Config Variables
- [ ] Check for usage of `$no_of_records_per_page`
- [ ] Check for usage of `$appname`
- [ ] Check for usage of `$translations`
- [ ] Check for usage of `$language`
- [ ] Check for usage of `$protocol`
- [ ] Check for usage of `$domain`

### Phase 3: Update config.php

Once all files have been migrated, simplify config.php to:

```php
<?php
/**
 * Configuration file for Viavi 8800SX application
 * 
 * This file simply loads the Config class.
 * All configuration is managed through Config::getInstance()
 * 
 * @author Viavi 8800SX
 */

// Load the Config class
require_once(__DIR__ . '/Config.php');

// No backward compatibility variables needed anymore
// All files now use Config::getInstance() directly
?>
```

### Migration Commands

Use these commands to help with the migration:

#### Find all files using $link
```bash
grep -n "\$link" data/web/app/*.php | grep -v "alert-link"
```

#### Find all files using $upload_target_dir
```bash
grep -n "\$upload_target_dir" data/web/app/*.php
```

#### Find all files using $translations
```bash
grep -n "\$translations" data/web/app/*.php
```

### Testing Strategy

For each file you migrate:

1. **Syntax Check**
   ```bash
   php -l data/web/app/filename.php
   ```

2. **Functional Test**
   - Test the functionality of the page in a browser
   - Verify database operations work
   - Verify file uploads work
   - Verify translations display correctly

3. **Regression Test**
   - Ensure no existing functionality breaks
   - Test all CRUD operations
   - Test navigation between pages

### Example Complete Migration

Here's a complete example of migrating `alignments-create.php`:

#### Step 1: Identify Current Usage
```php
// Current usage in alignments-create.php
require_once('config.php');  // Loads $link, $upload_target_dir
// ...
$stmt = $link->prepare("INSERT INTO ...");  // Uses $link
unlink($upload_target_dir . $result['success']);  // Uses $upload_target_dir
```

#### Step 2: Replace with Config Class
```php
// Updated alignments-create.php
require_once('Config.php');  // Load Config class
$config = Config::getInstance();  // Get instance
// ...
$stmt = $config->getDb()->prepare("INSERT INTO ...");  // Get DB from Config
unlink($config->getUploadTargetDir() . $result['success']);  // Get dir from Config
```

#### Step 3: Test
```bash
# Syntax check
php -l data/web/app/alignments-create.php

# Functional test
# 1. Navigate to the create page
# 2. Fill in the form
# 3. Submit and verify record is created
# 4. Verify file is uploaded to correct directory
```

## Why This Future Migration is Optional

The primary goal of removing global variable references has been achieved:

1. ✅ **Zero `global` keywords** in the codebase
2. ✅ **Helper functions don't use global state**
3. ✅ **Configuration is centralized in Config class**
4. ✅ **All syntax validation passes**

The backward compatibility layer in config.php:
- Does not use the `global` keyword
- Does not create hidden dependencies  
- Maintains clear data flow
- Is acceptable for production use

However, if you prefer a "pure" OOP approach without any top-level variables, the migration guide above will help you achieve that.

## Benefits of Complete Migration

If you choose to complete the migration:

1. **Explicit Dependencies Everywhere**
   - Every file explicitly states what configuration it needs
   - No implicit variable availability

2. **Better IDE Support**
   - Type hints work better
   - Autocomplete works for Config methods

3. **Easier Refactoring**
   - Config class can be easily modified
   - Dependencies are explicit

4. **Professional Code Structure**
   - Modern PHP OOP practices throughout
   - Consistent patterns across all files

## Recommendation

**For Most Users**: The current implementation is sufficient. The global keyword has been eliminated, which was the main goal.

**For Purists**: Follow this migration guide to eliminate the exported variables from config.php and use Config class directly everywhere.

Either approach is valid and production-ready. Choose based on your preferences and requirements.

## Questions?

If you need help with the migration or have questions about the Config class, refer to:
- `GLOBAL_VARIABLES_ANALYSIS.md` - Detailed analysis of the original problem
- `GLOBAL_VARIABLES_REMOVAL_SUMMARY.md` - Summary of what was accomplished
- `data/web/app/Config.php` - The Config class implementation with PHPDoc comments
