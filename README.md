# lamp-docker-compose

A fully containerized LAMP stack (Linux, Apache, MySQL, PHP) built with Docker Compose, demonstrating multi-container orchestration, container networking, and persistent data storage. Also includes a full observability stack with Prometheus and Grafana.

---

## What It Does

This project provisions a complete web application stack entirely in Docker:

- **Apache + PHP** — serves a PHP application via the official `php:8.2-apache` image
- **MySQL 8.0** — relational database with a persistent named volume so data survives container restarts
- **phpMyAdmin** — web-based database management UI for interacting with MySQL visually
- **Apache Exporter** — translates Apache's status page into Prometheus format
- **Prometheus** — scrapes and stores metrics every 15 seconds
- **Grafana** — visualizes metrics as live dashboards

On startup, the PHP application connects to MySQL and confirms the full stack is communicating correctly.

---

## Why I Built It

This is the first project in a portfolio I'm building toward SRE roles. My background is in LAMP-based infrastructure and AWS, but I hadn't worked directly with containerization in a previous role. The goal was to bridge that gap — taking a stack I already understood conceptually and rebuilding it using Docker, which is the foundation for everything else in this portfolio.

I also added Prometheus and Grafana because every company I've worked at used Datadog for observability but I'd never set up the underlying monitoring infrastructure myself. Building it from scratch gave me a much better understanding of how metrics collection actually works under the hood.

---

## Architecture

```
Browser
   |
   | http://localhost:8090
   |
[ webserver container ]  <-- built from Dockerfile
   Apache 2 + PHP 8.2
   volume: ./src -> /var/www/html
   |
   | internal Docker network (service name: db)
   |
[ db container ]
   MySQL 8.0
   volume: db_data -> /var/lib/mysql (persisted)
   |
[ phpmyadmin container ]  <-- http://localhost:8081
   connects to db via internal network

[ apache-exporter container ] :9117
   reads Apache /server-status
   |
[ prometheus container ] :9090
   scrapes metrics every 15s
   |
[ grafana container ] :3000
   visualizes metrics as dashboards
```

---

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running
- WSL 2 enabled (Windows users) with Docker integration turned on
- Git

---

## How to Run

```bash
git clone https://github.com/SirSquatchington/lamp-docker-compose
cd lamp-docker-compose
docker compose up --build
```

Once running, open your browser:

- `http://localhost:8090` — PHP application (confirms MySQL connection)
- `http://localhost:8081` — phpMyAdmin (login: `root` / `rootpassword`)
- `http://localhost:9090` — Prometheus UI and target health
- `http://localhost:3000` — Grafana (login: `admin` / `admin`)

To stop:

```bash
docker compose down
```

---

## Project Structure

```
lamp-docker-compose/
├── src/
│   └── index.php             # PHP app - connects to MySQL and renders confirmation
├── docker-compose.yml        # Defines and wires all six containers
├── Dockerfile                # Builds the Apache + PHP webserver image
├── prometheus.yml            # Prometheus scrape configuration
├── apache-status.conf        # Enables Apache status endpoint for scraping
└── README.md
```

---

## Observability Dashboard

Three Grafana panels showing live Apache metrics:

- **Apache Total Requests** — cumulative request count over time (`apache_accesses_total`)
- **Apache Workers** — busy, idle, and waiting worker counts (`apache_workers`)
- **Apache CPU Load** — CPU utilization of the Apache process (`apache_cpu_load`)

In Grafana, add Prometheus as a data source using `http://prometheus:9090` as the URL, then build dashboards using the metrics above.

---

## What I Ran Into

**Files in the wrong directory** — I initially created `docker-compose.yml` and `Dockerfile` inside the `src/` folder instead of the project root. Docker Compose expects them at the root and looks for `src/` as a subdirectory, so the volume mount was failing silently.

**Port conflict** — Port 8080 was already in use on my machine. Identified it with `ss -tlnp | grep 8080` and remapped the webserver to port 8090.

**Apache Forbidden error** — After getting containers running, Apache returned a 403. Resolved by ensuring correct project structure and file permissions.

**Apache's status page is locked down by default** — Enabling `mod_status` in the Dockerfile wasn't enough. Had to create `apache-status.conf` explicitly opening the `/server-status` endpoint and copy it into the container.

**The exporter pattern** — Prometheus can't scrape Apache directly since Apache doesn't natively expose metrics in Prometheus format. The Apache Exporter acts as a translator sidecar container — a pattern Prometheus uses for most third party services.

**ZSH interprets `?` as a wildcard** — When testing the Apache status page with curl, the `?` in the URL kept getting interpreted by ZSH as a file glob. The fix is to escape it: `server-status\?auto`.

---

## Technologies Used

- Docker / Docker Compose
- Apache HTTP Server 2.4
- PHP 8.2
- MySQL 8.0
- phpMyAdmin
- Prometheus
- Grafana
- Apache Exporter (bitnami/apache-exporter)
- WSL 2 (Ubuntu on Windows)
