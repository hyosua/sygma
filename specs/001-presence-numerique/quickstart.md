# Quickstart Guide

**Feature**: Digital Presence Management
**Date**: 2026-02-01

This guide provides instructions for setting up and running the project for local development.

## Prerequisites

- Docker
- Docker Compose
- A web browser (e.g., Chrome, Firefox)
- A code editor (e.g., VS Code)

## Setup and Installation

1.  **Clone the Repository**
    ```bash
    git clone <repository-url>
    cd Sygma
    ```

2.  **Backend Setup (Laravel)**
    - Navigate to the backend directory:
      ```bash
      cd backend
      ```
    - Copy the environment file:
      ```bash
      cp .env.example .env
      ```
    - *(Note: The following commands should be run inside the Docker container after it's been started)*.

3.  **Frontend Setup (React)**
    - Navigate to the frontend directory:
        ```bash
        cd frontend
        ```
    - *(Dependencies will be installed via the Docker container).*

## Running the Application with Docker

1.  **Build and Start Containers**
    - From the project root directory (`Sygma/`), run:
      ```bash
      docker-compose up --build -d
      ```
    - This will build the images for the `backend`, `frontend`, and `db` services and start them in detached mode.

2.  **Install Backend Dependencies**
    - Open a shell into the `backend` container:
      ```bash
      docker-compose exec backend bash
      ```
    - Inside the container, run the following commands:
      ```bash
      composer install
      php artisan key:generate
      php artisan migrate --seed # This runs database migrations and seeds initial data
      exit
      ```

3.  **Install Frontend Dependencies**
     - Open a shell into the `frontend` container:
      ```bash
      docker-compose exec frontend sh
      ```
    - Inside the container, run:
      ```bash
      npm install
      exit
      ```

## Accessing the Application

- **Frontend (React)**: [http://localhost:3000](http://localhost:3000)
- **Backend API**: [http://localhost:8000](http://localhost:8000)

## Default Login Credentials

- **Professor**: `professeur@example.com` / `password`
- **Student**: `etudiant@example.com` / `password`
- **Manager**: `gestionnaire@example.com` / `password`

*(These users will be created by the database seeder).*
