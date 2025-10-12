<?php
/**
 * Configuration management class for Viavi 8800SX application
 * 
 * This class encapsulates all application configuration and eliminates
 * the use of global variables. It uses the Singleton pattern to ensure
 * a single configuration instance throughout the application.
 * 
 * @author Viavi 8800SX
 */
class Config {
    private static $instance = null;
    
    // Database configuration
    private $dbConnection;
    private $dbServer;
    private $dbName;
    private $dbUser;
    private $dbPassword;
    
    // Application configuration
    private $translations;
    private $language;
    private $appName;
    private $noOfRecordsPerPage;
    private $protocol;
    private $domain;
    
    // Upload configuration
    private $uploadMaxSize;
    private $uploadTargetDir;
    private $uploadPersistentDir;
    private $uploadDisallowedExts;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->initializeDatabaseConfig();
        $this->initializeAppConfig();
        $this->initializeUploadConfig();
        $this->initializeDatabase();
    }
    
    /**
     * Get the singleton instance of Config
     * 
     * @return Config The singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }
    
    /**
     * Initialize database configuration
     */
    private function initializeDatabaseConfig() {
        $this->dbServer = 'db';
        $this->dbName = 'viavi';
        $this->dbUser = 'viavi';
        
        // Validate DB_PASSWORD_FILE environment variable
        $passwordFile = getenv("DB_PASSWORD_FILE");
        if (!$passwordFile) {
            throw new Exception("DB_PASSWORD_FILE environment variable is not set");
        }
        if (!file_exists($passwordFile)) {
            throw new Exception("Password file not found: " . $passwordFile);
        }
        $this->dbPassword = trim(file_get_contents($passwordFile));
    }
    
    /**
     * Initialize application configuration
     */
    private function initializeAppConfig() {
        $this->appName = '8800SX';
        $this->language = 'en';
        
        // Validate language code to prevent path traversal
        if (!preg_match('/^[a-z]{2}$/', $this->language)) {
            throw new Exception("Invalid language code: " . $this->language);
        }
        
        $this->noOfRecordsPerPage = 10; // Integer instead of string
        $this->protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
        $this->domain = $this->protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        
        // Load translations
        $localeFile = __DIR__ . "/locales/{$this->language}.php";
        if (!file_exists($localeFile)) {
            throw new Exception("Translation file not found: " . $localeFile);
        }
        $this->translations = include($localeFile);
    }
    
    /**
     * Initialize upload configuration
     */
    private function initializeUploadConfig() {
        $this->uploadMaxSize = 5000000; // default 5MB
        $this->uploadTargetDir = "uploads/"; // relative to core/app
        $this->uploadPersistentDir = true; // Do not delete uploads folder when regenerating CRUD files
        $this->uploadDisallowedExts = array(
            'php', 'php3', 'php4', 'php5', 'php7', 'phtml', // PHP and PHP-like files
            'html', 'htm', 'js', 'jsp', 'asp', 'aspx',      // HTML, JavaScript, and Server-side scripts
            'exe', 'bat', 'sh', 'bin',                      // Executable and shell script files
            'sql', 'sqlite', 'db',                          // Database files
            'htaccess', 'htpasswd',                         // Apache server files
            'pl', 'py', 'cgi',                              // Script files (Perl, Python, CGI)
            'jar', 'war', 'ear',                            // Java archives
            'vbs', 'ps1', 'psm1',                           // Script files (VBScript, PowerShell)
            'wsf', 'scf',                                   // Windows Script files
            'reg',                                          // Registry files
            'swf',                                          // Adobe Flash files
            'lnk',                                          // Windows shortcut files
        );
    }
    
    /**
     * Initialize database connection
     */
    private function initializeDatabase() {
        $this->dbConnection = mysqli_connect(
            $this->dbServer,
            $this->dbUser,
            $this->dbPassword,
            $this->dbName
        );
        
        if (!$this->dbConnection) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }
        
        // Set character set
        $query = "SHOW VARIABLES LIKE 'character_set_database'";
        if ($result = mysqli_query($this->dbConnection, $query)) {
            while ($row = mysqli_fetch_row($result)) {
                if (!$this->dbConnection->set_charset($row[1])) {
                    throw new Exception(
                        "Error loading character set " . 
                        htmlspecialchars($row[1]) . ": " . 
                        htmlspecialchars($this->dbConnection->error)
                    );
                }
            }
        }
    }
    
    // Getter methods for database
    public function getDb() {
        return $this->dbConnection;
    }
    
    public function getDbServer() {
        return $this->dbServer;
    }
    
    public function getDbName() {
        return $this->dbName;
    }
    
    public function getDbUser() {
        return $this->dbUser;
    }
    
    // Getter methods for application config
    public function getTranslations() {
        return $this->translations;
    }
    
    public function getLanguage() {
        return $this->language;
    }
    
    public function getAppName() {
        return $this->appName;
    }
    
    public function getNoOfRecordsPerPage() {
        return $this->noOfRecordsPerPage;
    }
    
    public function getProtocol() {
        return $this->protocol;
    }
    
    public function getDomain() {
        return $this->domain;
    }
    
    // Getter methods for upload config
    public function getUploadMaxSize() {
        return $this->uploadMaxSize;
    }
    
    public function getUploadTargetDir() {
        return $this->uploadTargetDir;
    }
    
    public function getUploadPersistentDir() {
        return $this->uploadPersistentDir;
    }
    
    public function getUploadDisallowedExts() {
        return $this->uploadDisallowedExts;
    }
    
    /**
     * Get all upload configuration as an array
     * 
     * @return array Upload configuration
     */
    public function getUploadConfig() {
        return [
            'max_size' => $this->uploadMaxSize,
            'target_dir' => $this->uploadTargetDir,
            'persistent_dir' => $this->uploadPersistentDir,
            'disallowed_exts' => $this->uploadDisallowedExts
        ];
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
