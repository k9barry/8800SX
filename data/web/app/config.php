<?php
/**
 * Configuration file for Viavi 8800SX application
 * 
 * This file now uses the Config class to manage all configuration
 * and has eliminated all global variables.
 * 
 * @author Viavi 8800SX
 */

// Load the Config class
require_once(__DIR__ . '/Config.php');

// Initialize configuration
$config = Config::getInstance();

// For backward compatibility, expose commonly used variables
// These will be removed in future versions as files are migrated
$link = $config->getDb();
$upload_target_dir = $config->getUploadTargetDir();
$no_of_records_per_page = $config->getNoOfRecordsPerPage();
$appname = $config->getAppName();
$translations = $config->getTranslations();
$language = $config->getLanguage();
$protocol = $config->getProtocol();
$domain = $config->getDomain();

?>
