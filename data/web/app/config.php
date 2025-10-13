<?php
/**
 * Configuration file for Viavi 8800SX application
 * 
 * This file provides backward compatibility by exposing configuration
 * variables for files that haven't been migrated to use Config::getInstance().
 * 
 * All configuration is managed through the Config class.
 * For new code, use Config::getInstance() directly instead of these variables.
 * 
 * @author Viavi 8800SX
 */

// Load the Config class
require_once(__DIR__ . '/Config.php');

// Initialize configuration
$config = Config::getInstance();

// ============================================================================
// DATABASE CONFIGURATION
// ============================================================================

/**
 * Database connection object (mysqli)
 * Used by all CRUD files for database operations
 */
$link = $config->getDb();

// ============================================================================
// APPLICATION CONFIGURATION
// ============================================================================

/**
 * Application name displayed in the UI
 */
$appname = $config->getAppName();

/**
 * Current language code (e.g., 'en', 'es', 'fr')
 */
$language = $config->getLanguage();

/**
 * Number of records to display per page in list views
 */
$no_of_records_per_page = $config->getNoOfRecordsPerPage();

/**
 * HTTP or HTTPS protocol for the current request
 */
$protocol = $config->getProtocol();

/**
 * Full domain URL including protocol (e.g., 'https://example.com')
 */
$domain = $config->getDomain();

/**
 * Translation strings for the current language
 * Used by the translate() helper function
 */
$translations = $config->getTranslations();

// ============================================================================
// FILE UPLOAD CONFIGURATION
// ============================================================================

/**
 * Maximum file size allowed for uploads (in bytes)
 */
$upload_max_size = $config->getUploadMaxSize();

/**
 * Target directory for uploaded files (relative to app directory)
 */
$upload_target_dir = $config->getUploadTargetDir();

/**
 * Whether to keep the uploads directory when regenerating CRUD files
 */
$upload_persistent_dir = $config->getUploadPersistentDir();

/**
 * Array of file extensions that are not allowed for upload
 * Includes executable files, scripts, and potentially dangerous file types
 */
$upload_disallowed_exts = $config->getUploadDisallowedExts();
