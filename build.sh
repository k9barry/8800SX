#!/bin/bash
# Build script for unified Docker image

set -e

echo "============================================"
echo "Building Viavi Unified Docker Image"
echo "============================================"
echo ""

# Default values
IMAGE_NAME="viavi"
IMAGE_TAG="unified"
DB_PASSWORD="ChangeMe"
CONTAINER_NAME="viavi"
PORT="8080"

# Parse command line arguments
while [[ $# -gt 0 ]]; do
  case $1 in
    --build-only)
      BUILD_ONLY=true
      shift
      ;;
    --no-cache)
      NO_CACHE="--no-cache"
      shift
      ;;
    --tag)
      IMAGE_TAG="$2"
      shift 2
      ;;
    --password)
      DB_PASSWORD="$2"
      shift 2
      ;;
    --port)
      PORT="$2"
      shift 2
      ;;
    --help)
      echo "Usage: $0 [OPTIONS]"
      echo ""
      echo "Options:"
      echo "  --build-only     Only build the image, don't run it"
      echo "  --no-cache       Build without using cache"
      echo "  --tag TAG        Set custom image tag (default: unified)"
      echo "  --password PASS  Set database password (default: ChangeMe)"
      echo "  --port PORT      Set host port (default: 8080)"
      echo "  --help           Show this help message"
      echo ""
      echo "Examples:"
      echo "  $0                                    # Build and run with defaults"
      echo "  $0 --build-only --no-cache            # Only build without cache"
      echo "  $0 --password MySecurePass --port 80  # Custom password and port"
      exit 0
      ;;
    *)
      echo "Unknown option: $1"
      echo "Use --help for usage information"
      exit 1
      ;;
  esac
done

# Build the image
echo "Step 1: Building Docker image..."
echo "Image: ${IMAGE_NAME}:${IMAGE_TAG}"
echo ""

if docker build ${NO_CACHE} -f Dockerfile -t ${IMAGE_NAME}:${IMAGE_TAG} .; then
    echo ""
    echo "✅ Image built successfully!"
else
    echo ""
    echo "❌ Build failed!"
    exit 1
fi

# Stop here if build-only
if [ "$BUILD_ONLY" = true ]; then
    echo ""
    echo "Build complete. Use the following command to run:"
    echo "docker run -d --name ${CONTAINER_NAME} -p ${PORT}:80 -e DB_PASSWORD=${DB_PASSWORD} ${IMAGE_NAME}:${IMAGE_TAG}"
    exit 0
fi

# Check if container already exists
if docker ps -a --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo ""
    echo "Step 2: Removing existing container..."
    docker stop ${CONTAINER_NAME} 2>/dev/null || true
    docker rm ${CONTAINER_NAME} 2>/dev/null || true
fi

# Run the container
echo ""
echo "Step 3: Starting container..."
echo "Container name: ${CONTAINER_NAME}"
echo "Port mapping: ${PORT}:80"
echo "Database password: ${DB_PASSWORD}"
echo ""

docker run -d \
  --name ${CONTAINER_NAME} \
  -p ${PORT}:80 \
  -e DB_PASSWORD="${DB_PASSWORD}" \
  -v viavi_data:/var/lib/mysql \
  -v viavi_uploads:/var/www/html/uploads \
  ${IMAGE_NAME}:${IMAGE_TAG}

echo ""
echo "✅ Container started successfully!"
echo ""
echo "============================================"
echo "Deployment Information"
echo "============================================"
echo "Container Name: ${CONTAINER_NAME}"
echo "Image: ${IMAGE_NAME}:${IMAGE_TAG}"
echo "Web Interface: http://localhost:${PORT}"
echo "Database Password: ${DB_PASSWORD}"
echo ""
echo "Useful Commands:"
echo "  View logs:        docker logs -f ${CONTAINER_NAME}"
echo "  Container shell:  docker exec -it ${CONTAINER_NAME} /bin/sh"
echo "  Stop container:   docker stop ${CONTAINER_NAME}"
echo "  Remove container: docker rm ${CONTAINER_NAME}"
echo "  Service status:   docker exec ${CONTAINER_NAME} supervisorctl status"
echo ""
echo "Waiting for services to start (this may take up to 60 seconds)..."

# Wait for container to be healthy
for i in {1..60}; do
    if docker inspect --format='{{.State.Health.Status}}' ${CONTAINER_NAME} 2>/dev/null | grep -q "healthy"; then
        echo ""
        echo "✅ All services are running and healthy!"
        echo ""
        echo "🌐 Open http://localhost:${PORT} in your browser"
        exit 0
    fi
    echo -n "."
    sleep 1
done

echo ""
echo "⚠️  Container is running but health check hasn't passed yet."
echo "Check logs with: docker logs ${CONTAINER_NAME}"
echo "You can still try accessing: http://localhost:${PORT}"
