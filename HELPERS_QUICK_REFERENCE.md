# helpers.php - Quick Reference Guide

## ✅ USED Functions (Keep These) - 6 Total

| Function | Usage Count | Files Using It |
|----------|-------------|----------------|
| `translate()` | 70+ | alignments-*.php, navbar.php, error.php, upload.php |
| `convert_datetime()` | 4 | alignments-index.php, alignments-read.php |
| `print_error_if_exists()` | 5 | alignments-delete.php, alignments-update.php, alignments-create.php |
| `handleFileUpload()` | 2 | alignments-update.php, alignments-create.php |
| `sanitize()` | Internal | Called by handleFileUpload() |
| `generateUniqueFileName()` | Internal | Called by handleFileUpload() |

## ❌ UNUSED Functions (Can Be Removed) - 7 Total

| Function | Reason Not Used |
|----------|-----------------|
| `parse_columns()` | No dynamic CRUD system implemented |
| `get_columns_attributes()` | No dynamic form generation |
| `convert_date()` | Redundant with convert_datetime() |
| `convert_bool()` | No boolean columns to display |
| `get_fk_url()` | No foreign key navigation implemented |
| `getUploadResultByErrorCode()` | Not integrated into upload flow |
| `truncate()` | No string truncation needed in views |

## Function Details

### translate($key, $echo = true, ...$args)
**Purpose:** Internationalization/localization
**Returns:** String or echoes translation
**Example:** `translate('Add New Record')`

### convert_datetime($date_str)
**Purpose:** Format datetime for display
**Returns:** 'Y-m-d H:i:s' formatted string
**Example:** `convert_datetime($row['datetime'])`

### print_error_if_exists($error)
**Purpose:** Display error messages in Bootstrap alerts
**Returns:** void (echoes HTML)
**Example:** `print_error_if_exists(@$error)`

### handleFileUpload($FILE)
**Purpose:** Secure file upload with validation
**Returns:** Array with 'success' or 'error' key
**Example:** `$result = handleFileUpload($_FILES['file'])`

### sanitize($fileName)
**Purpose:** Remove illegal characters from filename
**Internal:** Called by handleFileUpload()
**Returns:** Sanitized filename string

### generateUniqueFileName($originalFileName)
**Purpose:** Create unique filename with timestamp
**Internal:** Called by handleFileUpload()
**Returns:** Unique filename string

## Removal Impact Assessment

### Safe to Remove (No Dependencies)
- `parse_columns()` - 58 lines
- `get_columns_attributes()` - 18 lines
- `convert_date()` - 8 lines
- `convert_bool()` - 6 lines
- `get_fk_url()` - 15 lines
- `getUploadResultByErrorCode()` - 14 lines
- `truncate()` - 15 lines

**Total Lines Removable:** ~120 lines (43% of helpers.php)

### Potential Future Use Cases
- `truncate()` - Could be useful for displaying long filenames in tables
- `getUploadResultByErrorCode()` - Could improve error messaging
- `parse_columns()` - Could enable dynamic CRUD if needed in future

## Recommendation

**Conservative Approach:** Keep all functions (current state)
- Pros: Functions available if needed in future
- Cons: Code maintenance overhead, confusion about what's used

**Aggressive Approach:** Remove all 7 unused functions (~120 lines)
- Pros: Cleaner codebase, less to maintain
- Cons: Need to re-implement if needed later

**Balanced Approach:** Keep `truncate()` and `getUploadResultByErrorCode()`, remove 5 others (~91 lines)
- Pros: Keep potentially useful utilities, remove clearly unused code
- Cons: Still some unused code remains

## Search Commands Used

```bash
# Check function usage
grep -r "function_name" /path/to/app --include="*.php" | grep -v "helpers.php"

# Count usages
grep -r "translate(" /path/to/app --include="*.php" | grep -v "helpers.php" | wc -l

# List all functions
grep -n "^function " /path/to/helpers.php
```
