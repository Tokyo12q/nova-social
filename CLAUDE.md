# Nova Social - Development Guidelines

## 1. Tech Stack & Architecture

- **Backend (`/backend`)**:
  - Laravel 11 REST API
  - Laravel Sanctum (Authentication)
  - MySQL (Database)
  - Laravel Reverb (Realtime WebSocket events)
- **Frontend (`/frontend`)**:
  - Next.js
  - Tailwind CSS

---

## 2. Core Workflow Rules

- **One Feature at a Time**:
  - Always build ONE feature at a time.
  - Stop and await user review after completing each feature before proceeding to the next.
- **Manual Verification**:
  - After each task/feature, provide clear step-by-step instructions on how to test it manually (API requests, UI steps, or curl commands).
- **Package Management**:
  - **Do NOT install new packages** without explicit permission from the user. Always ask before running `composer require` or `npm install <package>`.
- **Language**:
  - All code, comments, documentation, and git commit messages must be in **English**.

---

## 3. Backend Architecture & Coding Standards

Every endpoint and feature in the backend must adhere to the following architecture:

- **Form Request Validation**:
  - Never validate inside controllers.
  - Every endpoint accepting input must use dedicated Form Request classes (`app/Http/Requests`).
- **API Resources**:
  - Never return raw Eloquent models or unformatted arrays directly from controllers.
  - Every response payload must be transformed via an API Resource (`app/Http/Resources`).
- **Authorization & Policies**:
  - Every endpoint requiring authorization must be protected via Laravel Policies (`app/Policies`).
- **Service Layer (Business Logic)**:
  - Controllers must remain thin and handle only HTTP orchestration (receiving requests, invoking services, returning API resources).
  - All core business logic must reside in dedicated Service classes (`app/Services`).
- **Testing**:
  - Every feature must include at least basic **Feature tests** (`tests/Feature`) covering happy paths and critical edge/authorization cases.
  - Ensure tests run cleanly before declaring a task complete.
