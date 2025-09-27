<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Integration tests for database functionality
 * 
 * Note: These tests require a test database to be available
 * They will be skipped if no database connection is available
 */
class DatabaseTest extends TestCase
{
    private $connection;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->connection = createTestDbConnection();
        if (!$this->connection) {
            $this->markTestSkipped('Test database not available');
        }
        
        // Create test table
        $this->createTestTable();
    }
    
    protected function tearDown(): void
    {
        if ($this->connection) {
            // Clean up test data
            $this->connection->query("DROP TABLE IF EXISTS alignments_test");
            $this->connection->close();
        }
        
        parent::tearDown();
    }
    
    private function createTestTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS alignments_test (
            id INT NOT NULL AUTO_INCREMENT,
            datetime DATETIME NOT NULL,
            model VARCHAR(25) NOT NULL,
            serial VARCHAR(25) NOT NULL,
            file BLOB NOT NULL,
            entered DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            filename VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE = InnoDB";
        
        $this->connection->query($sql);
    }
    
    public function testDatabaseConnection()
    {
        $this->assertNotNull($this->connection);
        $this->assertFalse($this->connection->connect_error);
    }
    
    public function testTableCreation()
    {
        $result = $this->connection->query("SHOW TABLES LIKE 'alignments_test'");
        $this->assertEquals(1, $result->num_rows);
    }
    
    public function testPreparedStatementInsert()
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO alignments_test (datetime, model, serial, file, filename) VALUES (?, ?, ?, ?, ?)"
        );
        
        $this->assertNotFalse($stmt, "Prepared statement should be created");
        
        $datetime = '2023-12-15 14:30:22';
        $model = 'APX8000';
        $serial = '12345678';
        $file = 'Test file content';
        $filename = 'APX8000-12345678-20231215-143022.txt';
        
        $result = $stmt->bind_param("sssss", $datetime, $model, $serial, $file, $filename);
        $this->assertTrue($result, "Parameters should bind successfully");
        
        $result = $stmt->execute();
        $this->assertTrue($result, "Statement should execute successfully");
        
        $this->assertEquals(1, $stmt->affected_rows);
        $stmt->close();
    }
    
    public function testPreparedStatementSelect()
    {
        // First insert test data
        $this->insertTestData();
        
        $stmt = $this->connection->prepare("SELECT * FROM alignments_test WHERE serial = ?");
        $this->assertNotFalse($stmt);
        
        $serial = '12345678';
        $stmt->bind_param("s", $serial);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $this->assertEquals(1, $result->num_rows);
        
        $row = $result->fetch_assoc();
        $this->assertEquals('APX8000', $row['model']);
        $this->assertEquals('12345678', $row['serial']);
        
        $stmt->close();
    }
    
    public function testSqlInjectionPrevention()
    {
        // Insert test data
        $this->insertTestData();
        
        // Try SQL injection attack
        $maliciousSerial = "12345678'; DROP TABLE alignments_test; --";
        
        $stmt = $this->connection->prepare("SELECT * FROM alignments_test WHERE serial = ?");
        $stmt->bind_param("s", $maliciousSerial);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $this->assertEquals(0, $result->num_rows, "SQL injection should not return results");
        
        // Verify table still exists
        $result = $this->connection->query("SHOW TABLES LIKE 'alignments_test'");
        $this->assertEquals(1, $result->num_rows, "Table should still exist after injection attempt");
        
        $stmt->close();
    }
    
    public function testDataIntegrity()
    {
        $testData = [
            'datetime' => '2023-12-15 14:30:22',
            'model' => 'APX8000',
            'serial' => '12345678',
            'file' => 'Test file content with special chars: <>&"\'',
            'filename' => 'APX8000-12345678-20231215-143022.txt'
        ];
        
        // Insert data
        $stmt = $this->connection->prepare(
            "INSERT INTO alignments_test (datetime, model, serial, file, filename) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", 
            $testData['datetime'], 
            $testData['model'], 
            $testData['serial'], 
            $testData['file'], 
            $testData['filename']
        );
        $stmt->execute();
        $insertId = $this->connection->insert_id;
        $stmt->close();
        
        // Retrieve and verify data
        $stmt = $this->connection->prepare("SELECT * FROM alignments_test WHERE id = ?");
        $stmt->bind_param("i", $insertId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $this->assertEquals($testData['model'], $row['model']);
        $this->assertEquals($testData['serial'], $row['serial']);
        $this->assertEquals($testData['file'], $row['file']);
        $this->assertEquals($testData['filename'], $row['filename']);
        
        $stmt->close();
    }
    
    public function testDuplicateFilenameDetection()
    {
        $filename = 'APX8000-12345678-20231215-143022.txt';
        
        // Insert first record
        $this->insertTestDataWithFilename($filename);
        
        // Check for duplicates
        $stmt = $this->connection->prepare("SELECT filename FROM alignments_test WHERE filename = ?");
        $stmt->bind_param("s", $filename);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $duplicates = [];
        while ($row = $result->fetch_row()) {
            $duplicates[] = $row[0];
        }
        
        $this->assertContains($filename, $duplicates);
        $stmt->close();
    }
    
    private function insertTestData()
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO alignments_test (datetime, model, serial, file, filename) VALUES (?, ?, ?, ?, ?)"
        );
        
        $datetime = '2023-12-15 14:30:22';
        $model = 'APX8000';
        $serial = '12345678';
        $file = 'Test file content';
        $filename = 'APX8000-12345678-20231215-143022.txt';
        
        $stmt->bind_param("sssss", $datetime, $model, $serial, $file, $filename);
        $stmt->execute();
        $stmt->close();
    }
    
    private function insertTestDataWithFilename($filename)
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO alignments_test (datetime, model, serial, file, filename) VALUES (?, ?, ?, ?, ?)"
        );
        
        $datetime = '2023-12-15 14:30:22';
        $model = 'APX8000';
        $serial = '12345678';
        $file = 'Test file content';
        
        $stmt->bind_param("sssss", $datetime, $model, $serial, $file, $filename);
        $stmt->execute();
        $stmt->close();
    }
}