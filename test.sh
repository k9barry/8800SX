#!/bin/bash
# Test script for unified Docker image

set -e

echo "============================================"
echo "Testing Viavi Unified Docker Image"
echo "============================================"
echo ""

# Configuration
IMAGE_NAME="${1:-viavi:test}"  # Accept image name as first argument, default to viavi:test
CONTAINER_NAME="viavi-test-$$"
DB_PASSWORD="test_password_123"
TEST_PORT="8081"
CLEANUP_ON_SUCCESS=true
SKIP_BUILD="${2:-false}"  # Accept skip build flag as second argument

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Cleanup function
cleanup() {
    echo ""
    echo "Cleaning up..."
    docker stop $CONTAINER_NAME 2>/dev/null || true
    docker rm $CONTAINER_NAME 2>/dev/null || true
}

# Set trap to cleanup on exit
trap cleanup EXIT

# Build the image (skip if image already exists and SKIP_BUILD is true)
if [ "$SKIP_BUILD" = "true" ]; then
    echo "Step 1: Using pre-built image: $IMAGE_NAME"
    # Verify the image exists
    if ! docker image inspect $IMAGE_NAME > /dev/null 2>&1; then
        echo -e "${RED}✗${NC} Image $IMAGE_NAME does not exist!"
        exit 1
    fi
    echo -e "${GREEN}✓${NC} Pre-built image found"
else
    echo "Step 1: Building Docker image..."
    if docker build -f Dockerfile -t $IMAGE_NAME . > /tmp/build.log 2>&1; then
        echo -e "${GREEN}✓${NC} Image built successfully"
    else
        echo -e "${RED}✗${NC} Build failed! Check /tmp/build.log"
        tail -50 /tmp/build.log
        exit 1
    fi
fi
echo ""

# Start container
echo "Step 2: Starting container..."
if docker run -d \
    --name $CONTAINER_NAME \
    -p $TEST_PORT:80 \
    -e DB_PASSWORD="$DB_PASSWORD" \
    $IMAGE_NAME > /dev/null; then
    echo -e "${GREEN}✓${NC} Container started"
else
    echo -e "${RED}✗${NC} Failed to start container"
    exit 1
fi
echo ""

# Wait for container to be healthy
echo "Step 3: Waiting for services to start..."
MAX_WAIT=60
for i in $(seq 1 $MAX_WAIT); do
    if docker exec $CONTAINER_NAME pgrep -x nginx > /dev/null 2>&1 && \
       docker exec $CONTAINER_NAME pgrep -x php-fpm > /dev/null 2>&1 && \
       docker exec $CONTAINER_NAME pgrep mariadbd > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC} All services are running (${i}s)"
        break
    fi
    if [ $i -eq $MAX_WAIT ]; then
        echo -e "${RED}✗${NC} Services failed to start within ${MAX_WAIT}s"
        echo "Container logs:"
        docker logs $CONTAINER_NAME
        exit 1
    fi
    echo -n "."
    sleep 1
done
echo ""

# Test web server
echo "Step 4: Testing web server..."
sleep 5  # Give nginx a moment to fully initialize
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:$TEST_PORT/ || echo "000")
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    echo -e "${GREEN}✓${NC} Web server responding (HTTP $HTTP_CODE)"
else
    echo -e "${RED}✗${NC} Web server not responding correctly (HTTP $HTTP_CODE)"
    docker logs $CONTAINER_NAME
    exit 1
fi
echo ""

# Test PHP-FPM
echo "Step 5: Testing PHP-FPM..."
if docker exec $CONTAINER_NAME php -v > /dev/null 2>&1; then
    PHP_VERSION=$(docker exec $CONTAINER_NAME php -v | head -1)
    echo -e "${GREEN}✓${NC} PHP-FPM working: $PHP_VERSION"
else
    echo -e "${RED}✗${NC} PHP-FPM test failed"
    exit 1
fi
echo ""

# Test database connection
echo "Step 6: Testing database..."
sleep 10  # Give database more time to initialize
if docker exec $CONTAINER_NAME mariadb -u root -e "SHOW DATABASES;" > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Database is accessible"
    
    # Check if viavi database exists
    if docker exec $CONTAINER_NAME mariadb -u root -e "SHOW DATABASES;" 2>/dev/null | grep -q "viavi"; then
        echo -e "${GREEN}✓${NC} 'viavi' database exists"
    else
        echo -e "${YELLOW}⚠${NC} 'viavi' database not found (may need manual initialization)"
    fi
    
    # Check if viavi user can connect
    if docker exec $CONTAINER_NAME mariadb -u viavi -p"$DB_PASSWORD" -e "SELECT 1;" > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC} Database user 'viavi' can connect"
    else
        echo -e "${YELLOW}⚠${NC} Database user 'viavi' cannot connect (may need manual setup)"
    fi
else
    echo -e "${RED}✗${NC} Database test failed"
    docker logs $CONTAINER_NAME
    exit 1
fi
echo ""

# Test file structure
echo "Step 7: Verifying file structure..."
REQUIRED_PATHS=(
    "/var/www/html/upload.php"
    "/var/www/html/app/config.php"
    "/docker-entrypoint-initdb.d/init-db.sql"
)

ALL_PATHS_OK=true
for path in "${REQUIRED_PATHS[@]}"; do
    if docker exec $CONTAINER_NAME test -f "$path"; then
        echo -e "${GREEN}✓${NC} $path exists"
    else
        echo -e "${RED}✗${NC} $path missing"
        ALL_PATHS_OK=false
    fi
done

if [ "$ALL_PATHS_OK" = false ]; then
    exit 1
fi
echo ""

# Summary
echo "============================================"
echo -e "${GREEN}All Tests Passed!${NC}"
echo "============================================"
echo ""
echo "Container Information:"
echo "  Name: $CONTAINER_NAME"
echo "  Image: $IMAGE_NAME"
echo "  URL: http://localhost:$TEST_PORT"
echo "  Database Password: $DB_PASSWORD"
echo ""
echo "Useful Commands:"
echo "  View logs:  docker logs $CONTAINER_NAME"
echo "  Shell:      docker exec -it $CONTAINER_NAME /bin/bash"
echo "  Stop:       docker stop $CONTAINER_NAME"
echo "  Remove:     docker rm $CONTAINER_NAME"
echo ""

# Keep container running or cleanup
if [ "$CLEANUP_ON_SUCCESS" = true ]; then
    echo "Cleaning up test container..."
    cleanup
    echo -e "${GREEN}✓${NC} Cleanup complete"
else
    echo -e "${YELLOW}Container left running for manual inspection${NC}"
    trap - EXIT  # Disable cleanup trap
fi

echo ""
echo "Testing complete!"
