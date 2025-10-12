# Helper Functions Usage Analysis

This document provides a comprehensive analysis of all functions defined in `data/web/app/helpers.php` and their usage throughout the repository.

## Summary

- **Total Functions Analyzed:** 13
- **Functions in Use:** 5
- **Functions NOT in Use:** 8

---

## Functions and Their Usage Status

### ✅ USED Functions (5)

#### 1. `translate($key, $echo = true, ...$args)`
**Status:** ✅ **USED** (heavily used throughout the application)

**Purpose:** Handles internationalization by translating keys to localized strings.

**Used in:**
- `data/web/app/alignments-index.php` - Multiple instances for UI text
- `data/web/app/alignments-delete.php` - Error messages
- `data/web/app/alignments-update.php` - Error messages and UI text
- `data/web/app/alignments-create.php` - Error messages and UI text
- `data/web/app/alignments-read.php` - UI text
- `data/web/app/alignments-view.php` - UI text
- `data/web/app/alignments-pdf.php` - Error messages
- `data/web/app/navbar.php` - Navigation text
- `data/web/app/error.php` - Error page text
- `data/web/app/upload.php` - Upload page text

**Example usage:**
```php
translate('Add New Record')
translate('%s Details', true, $str)
echo addslashes(translate('View File', false))
```

---

#### 2. `print_error_if_exists($error)`
**Status:** ✅ **USED**

**Purpose:** Displays error messages in Bootstrap alert divs.

**Used in:**
- `data/web/app/alignments-delete.php` - 1 instance
- `data/web/app/alignments-update.php` - 2 instances
- `data/web/app/alignments-create.php` - 2 instances

**Example usage:**
```php
<?php print_error_if_exists(@$error); ?>
<?php print_error_if_exists(@$upload_errors); ?>
```

---

#### 3. `convert_datetime($date_str)`
**Status:** ✅ **USED**

**Purpose:** Converts datetime strings to 'Y-m-d H:i:s' format with HTML escaping.

**Used in:**
- `data/web/app/alignments-index.php` - 2 instances (for 'datetime' and 'entered' columns)
- `data/web/app/alignments-read.php` - 2 instances (for 'datetime' and 'entered' columns)

**Example usage:**
```php
echo "<td>" . convert_datetime($row['datetime']) . "</td>";
echo convert_datetime($row["entered"]);
```

---

#### 4. `handleFileUpload($FILE)`
**Status:** ✅ **USED**

**Purpose:** Securely handles file uploads with validation and sanitization.

**Used in:**
- `data/web/app/alignments-update.php` - 1 instance
- `data/web/app/alignments-create.php` - 1 instance

**Example usage:**
```php
$this_upload = handleFileUpload($_FILES[$key]);
$this_upload = handleFileUpload($_FILES[$originalKey]);
```

**Note:** This function internally calls:
- `sanitize()` (line 167)
- `generateUniqueFileName()` (line 168)

---

#### 5. `sanitize($fileName)` and `generateUniqueFileName($originalFileName)`
**Status:** ✅ **USED** (internally by `handleFileUpload`)

**Purpose:** 
- `sanitize()` - Removes illegal characters from filenames
- `generateUniqueFileName()` - Creates unique filename with timestamp and salt

**Used in:**
- Called internally by `handleFileUpload()` function in `helpers.php`
- Not directly called from other files, but essential for file upload functionality

---

## ❌ UNUSED Functions (8)

### 1. `parse_columns($table_name, $postdata)`
**Status:** ❌ **NOT USED**

**Purpose:** Retrieves and enhances postdata table keys and values on CREATE and UPDATE events.

**Description:** This function queries INFORMATION_SCHEMA.COLUMNS to get column metadata and prepares default values for database operations.

**Potential Use Case:** Could be used in CRUD operations for dynamic form handling.

---

### 2. `get_columns_attributes($table_name, $column)`
**Status:** ❌ **NOT USED**

**Purpose:** Gets extra attributes (COLUMN_DEFAULT, COLUMN_COMMENT) for table columns on CREATE and UPDATE events.

**Description:** Queries INFORMATION_SCHEMA.COLUMNS for specific column metadata.

**Potential Use Case:** Could be used to dynamically generate forms with column-specific attributes.

---

### 3. `convert_date($date_str)`
**Status:** ❌ **NOT USED**

**Purpose:** Converts date strings to 'Y-m-d' format with HTML escaping.

**Description:** Similar to `convert_datetime()` but only formats the date part without time.

**Note:** The application uses `convert_datetime()` for all date/time formatting. This function may be redundant.

---

### 4. `convert_bool($var)`
**Status:** ❌ **NOT USED**

**Purpose:** Converts boolean values to "True" or "False" strings.

**Description:** Simple boolean to string converter for display purposes.

**Potential Use Case:** Could be used if the database had boolean columns that needed to be displayed as text.

---

### 5. `get_fk_url($value, $fk_table, $fk_column, $representation, bool $pk=false, bool $index=false)`
**Status:** ❌ **NOT USED**

**Purpose:** Generates URLs to foreign key parent's read or index page.

**Description:** Creates hyperlinks for foreign key relationships in database records.

**Potential Use Case:** Would be useful if the application had tables with foreign key relationships that needed to be navigable.

---

### 6. `getUploadResultByErrorCode($code)`
**Status:** ❌ **NOT USED**

**Purpose:** Returns human-readable error messages for PHP file upload error codes.

**Description:** Maps PHP upload error codes (0-8) to descriptive error messages.

**Note:** The `handleFileUpload()` function doesn't use this helper, but it could improve error messaging.

---

### 7. `truncate($string, $length = 15)`
**Status:** ❌ **NOT USED**

**Purpose:** Truncates strings to a specified length with ellipsis and HTML entity handling.

**Description:** Safely truncates strings while handling HTML entities and multibyte characters.

**Potential Use Case:** Could be used in table views to limit the display length of long text fields.

---

### 8. `convert_date($date_str)` - Duplicate Entry
**Status:** ❌ **NOT USED**

**Note:** This is listed separately from `convert_datetime()` above. The application only uses `convert_datetime()`.

---

## Recommendations

### Functions to Consider Removing (if unused)
These functions are not used anywhere in the codebase:

1. ❌ `parse_columns()` - Complex function with no current usage
2. ❌ `get_columns_attributes()` - No current usage
3. ❌ `convert_date()` - Redundant with `convert_datetime()`
4. ❌ `convert_bool()` - No boolean columns in use
5. ❌ `get_fk_url()` - No foreign key navigation implemented
6. ❌ `getUploadResultByErrorCode()` - Not used in file upload flow
7. ❌ `truncate()` - Not used for display

### Functions to Keep
These functions are actively used and should be retained:

1. ✅ `translate()` - Core i18n functionality
2. ✅ `print_error_if_exists()` - Error display
3. ✅ `convert_datetime()` - Date formatting
4. ✅ `handleFileUpload()` - File upload handling
5. ✅ `sanitize()` - Used by `handleFileUpload()`
6. ✅ `generateUniqueFileName()` - Used by `handleFileUpload()`

### Potential Improvements

1. **Consider using `getUploadResultByErrorCode()`**: The `handleFileUpload()` function could use this helper to provide better error messages for PHP upload errors.

2. **Consider using `truncate()`**: Long text fields in table views (like filenames or model names) could benefit from truncation for better UI display.

3. **Review `parse_columns()` and `get_columns_attributes()`**: These seem like they were intended for a more dynamic CRUD system. If not needed, they can be safely removed.

---

## Conclusion

Out of 13 functions in `helpers.php`:
- **5 functions are actively used** and critical to the application
- **8 functions are unused** and could potentially be removed to reduce code maintenance burden

The unused functions appear to be remnants of a more complex or generic CRUD system that may have been planned but not fully implemented. Removing them would simplify the codebase without affecting functionality.
