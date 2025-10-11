# Installation Guide

Follow these steps to install and set up the Viavi web application:

## Prerequisites
Before you begin, ensure you have the following installed on your system:
- Docker
- Docker Compose

## Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/k9barry/viavi.git
   ```
   Navigate to the project directory:
   ```bash
   cd viavi
   ```

2. **Set Up Environment Variables**
   - Create a `.env` file in the root directory of the project.
   - Add any necessary environment variables (e.g., database credentials, application settings).

3. **Build and Start Docker Containers**
   Run the following command to build and start the Docker containers:
   ```bash
   docker-compose up --build
   ```

4. **Access the Application**
   Once the containers are up and running, access the application in your web browser at:
   ```
   http://localhost:8000
   ```

5. **Stop the Application**
   To stop the Docker containers, run:
   ```bash
   docker-compose down
   ```

## Notes
- Ensure that the MySQL database container is correctly configured in the `docker-compose.yml` file.
- For troubleshooting, consult the application logs by running:
  ```bash
  docker-compose logs
  ```

For additional help, visit the [GitHub repository](https://github.com/k9barry/viavi).