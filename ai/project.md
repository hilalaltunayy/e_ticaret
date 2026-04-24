# Project Overview

This project is a modular e-commerce platform that supports both digital products (e-books) and physical products (printed books), uses role-based access control, separates the management panel from the customer-facing storefront, and is designed with a layered and maintainable architecture.

The system supports three main user types:
- Admin
- Secretary
- User (customer)

The goal of the project is not only to sell products, but to build a professional platform where the admin can manage both back-office operations and selected customer-facing interface structures in a controlled, scalable, and sustainable way.

---

# Core Vision

The platform is designed to combine the following within a single system:

- Physical and digital product sales
- Strong role and permission management
- Dynamic admin dashboard structure
- Admin-manageable storefront blocks
- Secure order and payment flow
- Digital content access control
- Reviews, favorites, and customer interactions
- Modular, maintainable, layered architecture

This project should not be treated as a simple online store. It is a broader platform that includes an admin panel, secretary access control, page builder, dashboard builder, order-stock-shipment core flows, and digital content access security.

---

# Scope

## Included Core Areas

- User, admin, and secretary roles
- Role- and permission-based access control
- Admin panel
- Limited and permission-driven secretary management screens
- Customer-facing storefront / website
- Printed and digital book sales logic
- Product listing and product detail pages
- Cart and checkout flow
- Order management
- Inventory management
- Shipment / shipping management
- Favorites
- Reviews and ratings
- Review moderation
- Admin dashboard builder
- Admin page builder
- Admin-manageable storefront blocks
- Reader logic for digital books
- Watermark, token, and access control
- Audit log / traceability
- Layered CodeIgniter 4 architecture

## Out of Scope for Now

- Native mobile application
- Third-party marketplace integrations
- Multi-language support
- Multi-currency support
- Fully developed AI recommendation engine
- Real DRM-grade protection
- Mandatory production-grade payment provider integration
- Large-scale microservice architecture

Note: AI-based modules may be added later. For now, the priority is to stabilize the core commerce and management architecture.

---

# Main User Roles

## 1. Admin

The admin is the highest-privileged user in the system.

Responsibilities:
- Dashboard management
- Dashboard builder usage
- Page builder usage
- Product management
- Inventory management
- Order management
- Shipment management
- Campaign and content areas
- User management
- Secretary account creation
- Granting and revoking secretary permissions
- Managing selected customer-facing page structures and content
- Controlling draft/publish flows where required

## 2. Secretary

The secretary is not an admin. The visible areas for the secretary are not fixed; they depend on the permissions granted by the admin.

Core approach:
- Minimum access by default
- User-specific permission overrides assigned by admin
- Access only to explicitly allowed modules
- No permission bypass by entering URLs manually
- All access must be enforced at route/filter and service level

The secretary may operate in areas such as:
- Review moderation
- Specific parts of order operations
- Shipment or operational updates
- Limited management areas explicitly allowed by the admin

## 3. User (Customer)

The customer uses the storefront.

Core capabilities:
- Browse and view products
- Inspect product details
- Add items to favorites
- Add items to cart
- Purchase products
- View own orders
- Write reviews
- Rate products
- Access digital purchases
- Manage account details

---

# RBAC and Permission Model

The project uses role-based access control (RBAC).

Core data structures:
- users
- roles
- permissions
- role_permissions
- user_roles
- user_permissions

Core principles:
- Admin / secretary / user separation must remain clear
- Permission checks must be centralized
- Deny by default must be enforced
- Route-level filters must exist
- Critical actions should also have service-level guards
- Secretary access must support user-specific overrides

In this project, the permission system is not only about login redirection. It is a core security layer that must protect the full lifecycle of management screens and business actions.

---

# Product and Commerce Model

The system supports both physical and digital products.

## Core product-side separation

- Work / Publication: content identity
- SKU: sellable variant
- PRINT / DIGITAL / BUNDLE logic
- DigitalAsset: digital file metadata
- Inventory: stock data for printed products

The purpose of this separation is to:
- Manage multiple sellable formats of the same work
- Apply stock rules only to printed products
- Handle digital access logic separately
- Build more sustainable cart and order flows

---

# Storefront Goals

The user-facing side is not treated as a simple storefront, but as a controlled and extendable customer experience layer.

Main screens:
- Home
- Product listing
- Product detail
- Favorites
- Cart
- Checkout
- My orders
- My account
- Digital reader screen

Targets for these pages:
- Modern, clean, minimal interface
- Partially admin-manageable structure
- Controlled flexibility for product cards and product detail layouts
- Mobile-friendly behavior
- High user experience with low maintenance cost

---

# Page Builder and Frontend Management

One of the major differentiators of this project is that the admin can manage not only panel content, but also selected storefront pages through a block-based system.

Targeted manageable pages:
- home
- product_list
- product_detail
- cart
- checkout
- other storefront screens when necessary

Core structures:
- pages
- page_versions
- block_types
- block_instances
- media_assets
- theme_tokens

Core logic:
- Page layouts are versioned
- Draft / published separation exists
- Block-based management is used
- Configuration validation is required
- Admin misconfiguration must not break the system
- Rendering must work through controlled partial/view logic

For product cards, cart, checkout and product detail layouts, the preferred approach is controlled slot-based flexibility rather than unrestricted visual freedom.

---

# Admin Dashboard Builder

The admin dashboard is also block-based and configurable.

The dashboard system should support:
- Cards, charts, sliders, and similar blocks
- Data-source-driven rendering
- Configuration-based display control
- Extension according to versioning/publishing logic
- Performance optimization through caching when needed

The goal is not just to create a visually appealing panel, but a dynamic management area that supports operational workflows.

Additionally, the admin can build their own dashboard, rearranging blocks using drag-and-drop, adjusting their sizes, and customizing their appearance.

---

# Cart, Order, Payment, and Shipment Core

The real commerce backbone of the project consists of:
- Cart / CartItem
- Order / OrderItem
- Payment / PaymentAttempt
- Shipment / ShipmentEvent
- Campaign / Coupon / Redemption
- Return / Refund

Core principles:
- The cart must be designed correctly from the start
- Checkout should reprice if prices have changed
- Snapshot logic must be preserved in order items
- Payment callback / webhook handling must be idempotent
- Shipment flow should be event-oriented
- Edge cases such as stock race conditions must be considered

This project is not only UI-focused; order integrity and data correctness are critical.

---

# Digital Reading and Access Security

The digital product side is one of the most distinctive parts of the project.

Goals:
- Allow customers to read digital content
- Prevent direct raw file access
- Enforce access control
- Make copying harder
- Preserve traceability

Approach:
- PDF.js or similar canvas-based rendering
- Controlled rendering approach for EPUB
- Storing files outside the webroot
- Token-based access
- Watermarking
- Access logs
- Revoke / expire logic when needed
- Rate limiting

Note:
This is not full DRM. The real goal is deterrence, control, and traceability rather than absolute prevention.

---

# Reviews, Favorites, and Customer Interaction

The storefront must include key interaction features such as:
- Favorites
- Product rating
- Product reviews
- View tracking
- Moderation workflow

The secretary role is especially important for review moderation.

The review system should support:
- PENDING
- APPROVED
- HIDDEN
- Soft delete where necessary
- Moderation action logging

---

# Audit Log and Traceability

In this project, audit logging is not a luxury feature. It is a required operational layer, especially for permission changes and critical business actions.

Actions that should be logged include:
- Permission grant / revoke
- Product price changes
- Inventory changes
- Order status changes
- Shipment status changes
- Review delete / hide / approve actions
- Dashboard publish actions
- Page publish actions
- Digital access revoke actions

Goals:
- Answer who did what and when
- Keep secretary actions reviewable
- Support troubleshooting and operational traceability

---

# Architectural Approach

The project is built on CodeIgniter 4 using a layered architecture approach.

Core principles:
- Thin controllers
- Service-heavy business logic
- Auth and permission gates through filters/middleware
- Controlled data flow through DTOs / validators
- Data access via model/repository layer
- Transactions where necessary
- Reusable partial/view component structure
- Controlled schema management through migrations and seeders

Controllers should remain focused on request/response handling only. Business rules should live in the service layer.

However, the service layer should not be unnecessarily bloated; it should grow to a maximum of 700-800 lines. New services should be created when necessary, but this doesn't mean constantly creating new services. A controlled approach is essential.
---

# Security Approach

Security is not an afterthought in this project. It is a core part of the system design.

Priority security areas:
- RBAC and object-level authorization
- Route filter protection
- Service-level authorization checks
- CSRF protection
- Output encoding / sanitization against XSS
- Extra care in builder-configurable areas
- Secure headers
- Session security
- File upload security
- Secrets and key management
- Error handling
- Rate limiting
- Protection of digital reader endpoints

The goal is not only to resist attacks, but also to reduce misconfiguration, permission leakage, and accidental data exposure.

---

# State Machines and Workflow

Several core entities in the project should follow state-machine-based transitions.

Important status areas:
- User.status
- SKU.status
- Order.status
- Payment.status
- Shipment.status
- DigitalAccess.status
- Review.status

The goal of this approach is to:
- Prevent random and invalid status transitions
- Keep workflows rule-driven
- Make transition rules explicit inside the service layer

---

# Current Development State

This project is being developed iteratively through AI-assisted sprint planning and controlled implementation.

General state:
- The admin panel is largely shaped and close to the desired level
- Admin and secretary roles have been separated conceptually and structurally
- The user-facing side is the current active development area
- Frontend work is progressing incrementally across screens such as home, product list, product detail, cart, and checkout
- Page management / builder logic has an important role inside the project
- Changes are being planned and implemented through small, controlled sprints

This document reflects not only a theoretical vision, but the active structure of the system being built.

---

# Development Method

The project advances using the following workflow:
- Clarify scope and target first
- Break work into small and manageable sprints
- Prepare tasks and sprint definitions with AI assistance
- Implement using Codex or similar tools
- Manually review the output
- Fix through small iterations if necessary
- Make large structural decisions in a controlled way

The objective is not to produce perfect code in one shot. The objective is to move with controlled speed, correct architecture, and sustainable quality.

---

# Long-Term Expansion Areas

Potential future additions include:
- AI-powered recommendation system
- AI-assisted content helper for the admin panel
- Intelligent moderation assistance for secretary workflows
- Advanced campaign engine
- Advanced analytics
- Real payment provider integrations
- More advanced digital rights controls
- Notification and automation infrastructure

These areas should only be expanded after the core system becomes stable.

---

# Success Criteria

The project is considered successful if it achieves the following balance:
- Strong but not overcomplicated admin panel
- Controlled permission architecture
- Clean and modern customer experience
- Manageable storefront structure
- Solid order / inventory / access backbone
- Extendable but not fragile architecture
- AI-accelerated development with human control preserved
