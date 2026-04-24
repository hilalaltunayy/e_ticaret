# CodeIgniter 4 Controller-Service Pattern Skill

This skill defines how to structure Controllers, Services, Models, and Views in a clean architecture.

## Purpose

Keep controllers thin, move business logic to services, and improve maintainability and testability.

## Architecture Responsibilities

### Controller
- Handles HTTP request/response
- Reads input data
- Prepares DTO or arrays
- Calls service layer
- Returns view, redirect, or JSON
- Must NOT contain business logic

### Service
- Contains business logic
- Performs permission checks
- Handles transactions
- Calls models/repositories
- Triggers audit logging if needed

### Model
- Handles database access
- Performs CRUD operations
- Should not contain business decision logic

### View
- Displays data
- May hide UI elements based on permission
- Must NOT be the only layer enforcing security

## Rules

- Do not write business logic in controllers
- Do not access DB directly from controllers
- Always perform critical permission checks in service layer
- State transitions (order, stock, publish, etc.) must be in services
- Views are presentation only
- Always consider route and filter when adding controllers
- Keep services focused and reusable
- Do not expose internal errors to users

## Flow Example

Route
→ Filter
→ Controller
→ Service
→ Model
→ Database
→ Service Result
→ Controller Response

## Usage in This Project

This project uses admin, secretary, and user roles.

Examples:

- ProductController → ProductService
- OrderController → OrderService
- SecretaryReviewController → ReviewModerationService
- PageBuilderController → PageBuilderService
