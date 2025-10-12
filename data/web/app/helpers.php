<?php
// Helper functions for the Viavi 8800SX application

function print_error_if_exists($error)
{
    if (isset($error)) {
        if (!is_array($error)) {
            echo "<div class='alert alert-danger' role='alert'>$error</div>";
        } else {
            foreach ($error as $err) {
                echo "<div class='alert alert-danger' role='alert'>$err</div>";
            }
        }
    }
}

function convert_datetime($date_str)
{
    if (isset($date_str)) {
        //$date = date('d-m-Y H:i:s', strtotime($date_str));
        $date = date('Y-m-d H:i:s', strtotime($date_str));
        return htmlspecialchars($date);
    }
}

function translate($key, $echo = true, ...$args)
{
    $config = Config::getInstance();
    $translations = $config->getTranslations();

    // Check if the key exists in the array
    if (isset($translations[$key])) {
        if ($echo) {
            echo sprintf($translations[$key], ...$args);
        } else {
            return sprintf($translations[$key], ...$args);
        }
    } else {
        // echo key itself if translation not found
        if ($echo) {
            echo $key;
        } else {
            return $key;
        }
    }
}




function handleFileUpload($FILE) {

    $config = Config::getInstance();
    $upload_max_size = $config->getUploadMaxSize();
    $upload_target_dir = $config->getUploadTargetDir();
    $upload_disallowed_exts = $config->getUploadDisallowedExts();

    $upload_results     = array();
    
    // Check for PHP upload errors first
    if (isset($FILE["error"]) && $FILE["error"] !== UPLOAD_ERR_OK) {
        $upload_results['error'] = getUploadResultByErrorCode($FILE["error"]);
        return $upload_results;
    }
    
    $sanitized_fileName = sanitize(basename($FILE["name"]));
    $unique_filename    = generateUniqueFileName($sanitized_fileName);
    $target_file        = $upload_target_dir . $unique_filename;
    $extension          = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the upload directory exists
    if (!file_exists($upload_target_dir)) {
        // The 0777 permission will be modified by your umask
        mkdir($upload_target_dir, 0777, true);

        // Write a dummy index file to prevent directory listing
        file_put_contents($upload_target_dir . '/index.php', '');
        // $upload_results['error'] = "Upload directory created.";
    } else {
        // $upload_results['error'] = "Upload directory already exists.";
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        $upload_results['error'] = "Sorry, the file " . htmlspecialchars(basename($FILE["name"])) . " already exists.";
        return $upload_results;
    }

    // Check file size (example: 5MB limit)
    if ($FILE["size"] > $upload_max_size) {
        $upload_results['error'] = "Sorry, the file " . htmlspecialchars(basename($FILE["name"])) . " is too large.";
        return $upload_results;
    }

    // Extensions blacklist
    if (in_array($extension, $upload_disallowed_exts)) {
        $upload_results['error'] = "Sorry, uploading files with extension $extension is not allowed.";
        return $upload_results;
    }

    // Try to upload file
    if (empty($upload_results)) {
        if (move_uploaded_file($FILE["tmp_name"], $target_file)) {
            $upload_results['success'] = $unique_filename;
        } else {
            $upload_results['error'] = "Sorry, there was an error uploading the file " . htmlspecialchars(basename($FILE["name"])) . ".";
        }
    }
    return $upload_results;
}



function sanitize($fileName) {
    // Remove illegal file system characters
    $fileName = str_replace(array('<', '>', ':', '"', '/', '\\', '|', '?', '*'), '', $fileName);

    // Normalize Unicode characters
    if (class_exists('Normalizer')) {
        $fileName = Normalizer::normalize($fileName, Normalizer::FORM_C);
    }

    // Replace spaces with underscores
    $fileName = str_replace(' ', '_', $fileName);

    // Convert to lowercase for consistency
    $fileName = strtolower($fileName);

    // Truncate to a maximum length to avoid system limitations (255 characters is a safe bet)
    $fileName = substr($fileName, 0, 255);

    return $fileName;
}



function generateUniqueFileName($originalFileName) {
    $timestamp = time();
    $salt = uniqid(); // Alternatively, use bin2hex(random_bytes(8)) for more randomness
    $uniquePrefix = $timestamp . '_' . $salt . '_';

    return $uniquePrefix . $originalFileName;
}



function getUploadResultByErrorCode($code) {
    // https://www.php.net/manual/en/features.file-upload.errors.php
    $phpFileUploadErrors = array(
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    );
    return $phpFileUploadErrors[$code];
}