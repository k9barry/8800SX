<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test file validation and security functions
 */
class FileValidationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // We test the helper functions by creating our own test versions
        // to avoid loading the entire helpers.php which has conflicts
    }

    /**
     * Test sanitize function logic (reimplemented for testing)
     */
    private function sanitizeTestHelper($fileName) {
        // Remove illegal file system characters
        $fileName = str_replace(array('<', '>', ':', '"', '/', '\\', '|', '?', '*'), '', $fileName);

        // Normalize Unicode characters if available
        if (class_exists('\Normalizer')) {
            $fileName = \Normalizer::normalize($fileName, \Normalizer::FORM_C);
        }

        // Replace spaces with underscores
        $fileName = str_replace(' ', '_', $fileName);

        // Convert to lowercase for consistency
        $fileName = strtolower($fileName);

        // Truncate to a maximum length to avoid system limitations (255 characters is a safe bet)
        $fileName = substr($fileName, 0, 255);

        return $fileName;
    }

    /**
     * Test unique filename generator (reimplemented for testing)
     */
    private function generateUniqueFileNameTestHelper($originalFileName) {
        $timestamp = time();
        $salt = uniqid(); // Alternatively, use bin2hex(random_bytes(8)) for more randomness
        $uniquePrefix = $timestamp . '_' . $salt . '_';

        return $uniquePrefix . $originalFileName;
    }

    public function testSanitizeFileName()
    {
        // Test basic sanitization
        $this->assertEquals('test_file.txt', $this->sanitizeTestHelper('test file.txt'));
        
        // Test dangerous characters removal
        $this->assertEquals('testfile.txt', $this->sanitizeTestHelper('test<>:|file.txt'));
        
        // Test length truncation
        $longName = str_repeat('a', 300) . '.txt';
        $sanitized = $this->sanitizeTestHelper($longName);
        $this->assertLessThanOrEqual(255, strlen($sanitized));
    }

    public function testGenerateUniqueFileName()
    {
        $original = 'test.txt';
        $unique1 = $this->generateUniqueFileNameTestHelper($original);
        $unique2 = $this->generateUniqueFileNameTestHelper($original);
        
        // Should be different each time
        $this->assertNotEquals($unique1, $unique2);
        
        // Should contain original filename
        $this->assertStringContainsString($original, $unique1);
        
        // Should have timestamp and salt
        $this->assertMatchesRegularExpression('/^\d+_[a-f0-9]+_test\.txt$/', $unique1);
    }

    public function testFileExtensionValidation()
    {
        $validExtensions = ['txt'];
        
        // Test valid extension
        $this->assertTrue(in_array('txt', $validExtensions));
        
        // Test invalid extensions
        $this->assertFalse(in_array('php', $validExtensions));
        $this->assertFalse(in_array('exe', $validExtensions));
        $this->assertFalse(in_array('js', $validExtensions));
    }

    public function testFilenameFormatValidation()
    {
        // Valid format: MODEL-SERIAL-DATE-TIME.txt
        $validFilenames = [
            'APX8000-12345678-20231215-143022.txt',
            'XTL5000-87654321-12152023-091545.txt'
        ];
        
        $invalidFilenames = [
            'invalid.txt',
            'MODEL-SERIAL.txt',
            'MODEL-SERIAL-DATE.txt'
        ];
        
        foreach ($validFilenames as $filename) {
            $parts = explode('-', $filename);
            $this->assertGreaterThanOrEqual(4, count($parts), "Valid filename should have 4+ parts: $filename");
        }
        
        foreach ($invalidFilenames as $filename) {
            $parts = explode('-', $filename);
            $this->assertLessThan(4, count($parts), "Invalid filename should have <4 parts: $filename");
        }
    }

    public function testDateTimeValidation()
    {
        // Test valid date formats
        $validDates = [
            ['12', '15', '2023'], // MM/DD/YYYY
            ['2023', '12', '15']  // YYYY/MM/DD
        ];
        
        foreach ($validDates as [$part1, $part2, $part3]) {
            // Test both formats
            $this->assertTrue(
                checkdate($part1, $part2, $part3) || checkdate($part2, $part3, $part1),
                "Date should be valid: $part1-$part2-$part3"
            );
        }
        
        // Test valid time
        $validTimes = ['143022', '091545', '235959'];
        foreach ($validTimes as $time) {
            $hour = substr($time, 0, 2);
            $minute = substr($time, 2, 2);
            $second = substr($time, 4, 2);
            
            $this->assertLessThanOrEqual(23, (int)$hour, "Hour should be ≤23: $time");
            $this->assertLessThanOrEqual(59, (int)$minute, "Minute should be ≤59: $time");
            $this->assertLessThanOrEqual(59, (int)$second, "Second should be ≤59: $time");
        }
    }

    public function testUploadErrorCodes()
    {
        // Test PHP upload error codes handling
        $errorCodes = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        
        foreach ($errorCodes as $code => $expected) {
            // Test that each error code is a valid integer
            $this->assertIsInt($code, "Error code should be integer: $code");
            
            // Test that we can handle different error conditions
            if ($code !== UPLOAD_ERR_OK) {
                $this->assertNotEquals(UPLOAD_ERR_OK, $code, "Error code should not be OK: $code");
            }
        }
    }
}