lamp-docker-compose
A fully containerized LAMP stack (Linux, Apache, MySQL, PHP) built with Docker Compose, demonstrating multi-container orchestration, container networking, and persistent data storage.

What It Does
This project provisions a three-tier web application stack entirely in Docker:
    • Apache + PHP — serves a PHP application via the official php:8.2-apache image 
    • MySQL 8.0 — relational database with a persistent named volume so data survives container restarts 
    • phpMyAdmin — web-based database management UI for interacting with MySQL visually 
On startup, the PHP application connects to MySQL and confirms the full stack is communicating correctly.

Why I Built It
This is the first project in a portfolio I'm building toward SRE roles. My background is in LAMP-based infrastructure and AWS, but I hadn't worked directly with containerization in a previous role. The goal here was to bridge that gap — taking a stack I already understood conceptually and rebuilding it using Docker, which is the foundation for everything else in this portfolio (CI/CD pipelines, cloud deployment, and Kubernetes down the road).
I also wanted to understand Docker Compose specifically — how multi-container applications are defined, how container networking works, and how to make an environment fully reproducible with a single command.

Architecture
Browser
   |
   | http://localhost:8090
   |
[ webserver container ]  ←── built from Dockerfile
   Apache 2 + PHP 8.2
   volume: ./src → /var/www/html
   |
   | internal Docker network (service name: db)
   |
[ db container ]
   MySQL 8.0
   volume: db_data → /var/lib/mysql (persisted)
   |
[ phpmyadmin container ]  ←── http://localhost:8081
   connects to db via internal network
All three containers share a Docker-managed internal network and communicate using service names rather than IP addresses — db resolves automatically to the MySQL container.

Prerequisites
    • Docker Desktop installed and running 
    • WSL 2 enabled (Windows users) with Docker integration turned on 
    • Git 

How to Run
git clone https://github.com/matyerkes/lamp-docker-compose
cd lamp-docker-compose
docker compose up --build
That's it. Docker will build the webserver image and pull MySQL and phpMyAdmin from Docker Hub.
Once running, open your browser and go to:
    • http://localhost:8090 — PHP application (confirms MySQL connection) 
    • http://localhost:8081 — phpMyAdmin (login: root / rootpassword) 
To stop:
docker compose down

Project Structure
lamp-docker-compose/
├── src/
│   └── index.php        # PHP app — connects to MySQL and renders confirmation
├── docker-compose.yml   # Defines and wires all three containers
├── Dockerfile           # Builds the Apache + PHP webserver image
└── README.md

What I Ran Into
Files in the wrong directory — I initially created the docker-compose.yml and Dockerfile inside the src/ folder instead of the project root. Docker Compose expects to find them at the root and looks for src/ as a subdirectory from there, so the volume mount was failing silently. Moving them up one level fixed it.
Port conflict — Port 8080 was already in use on my machine by another process. I identified it using ss -tlnp | grep 8080 and remapped the webserver to port 8090 in docker-compose.yml.
Apache Forbidden error — After getting the containers running, Apache was returning a 403. The volume mount wasn't making index.php visible inside the container. This turned out to be a combination of the directory structure issue above and file permissions — resolved by ensuring the correct project structure and running chmod -R 755 on the src/ directory.
These were all good learning moments — the kind of environment-level debugging that doesn't show up in tutorials but comes up constantly in real infrastructure work.

Technologies Used
    • Docker / Docker Compose 
    • Apache HTTP Server 2.4 
    • PHP 8.2 
    • MySQL 8.0 
    • phpMyAdmin 
    • WSL 2 (Ubuntu on Windows) 
