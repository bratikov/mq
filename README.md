# Coroutine based Message Queue

## Overview
This project is a high-performance message queue built using PHP and Swoole. Unlike traditional message queue solutions which uses RabbitMQ for example, where running multiple queue listeners requires spawning multiple PHP processes, this implementation optimizes resource usage by running a single process. Inside this process, a configurable number of channels handle messages in parallel using coroutines, avoiding the overhead of excessive process creation.

## Key Features
- Single-process architecture: Eliminates the need to start multiple PHP processes for each queue listener.
- Coroutine-based parallel processing: Efficiently handles multiple messages without blocking.
- Fast message enqueueing: Can enqueue 100,000 messages in just a few seconds.
- Configurable channels: Users can specify the number and size of channels to optimize processing.
- Logging: Execution results are logged for monitoring and debugging.
- Task recovery: The queue system restores pending tasks after a server restart, ensuring reliability.
- Console interface: A built-in CLI tool allows monitoring of channels and queue status.

## Installation
To install the project in your own application, you need to update your composer.json as follows:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:bratikov/mq.git"
        }
    ],
    "require": {
        "bratikov/mq": "^1.0"
    }
}
```

## Usage Examples
Basic usage examples can be found in the examples' folder. To simplify the process, additional scripts are provided, and you can use the Docker image bratikov/php:8.4-swoole for easy setup.

### Running the Example
1. **Clone**:  
    ```bash
    git clone git@github.com:bratikov/mq.git
    ```
2. **Install dependencies**:  
    This script runs composer and installs all required dependencies
    ```bash
    ./bin/composer update
    ```
3. **Start the Docker container**:  
    This script mounts the project folder into the container.
    ```bash
    ./examples/start
    ```
4. **Start the server**:  
    Run the server script inside the container.
    ```bash
    docker exec -it mq /mq/examples/server.php
    ```
5. **Send 100,000 messages**:  
    Run the client script to enqueue messages.
    ```bash
    docker exec -it mq /mq/examples/client.php
    ```
6. **Monitor the queue in real-time**:  
    Track the status of the queue.
    ```bash
    docker exec -it mq /mq/bin/mq status -f
    ```
7. **Stop the Docker container**:  
    Stop the running container.
    ```bash
    ./examples/stop
    ```

## Important Notes
- In this project, a message is a **serialized PHP object** that is placed in the queue. When the message reaches the handler, it is deserialized and executed.
- **Initialize all variables** that will be used in the task before placing them in the queue to avoid issues with deserialization.
- The **server must be run in the same environment** and with the **same autoloader** that the original project uses to ensure compatibility.
- If you modify the source code of the tasks (messages), you must **restart the server** due to opcache caching the compiled PHP code.
- When working with connections to resources such as databases, Redis, and others, where connections are used, after deserializing the object, the connections should either be **recreated** or the existing connection should be **reloaded** (closed and reopened) to ensure proper functionality.
    