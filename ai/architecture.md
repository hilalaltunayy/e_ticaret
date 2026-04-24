# Architecture

This document defines the real technical working structure of the project.
Its purpose is to clarify which layers exist in the system, how these layers communicate, how the main modules are positioned, and which architectural flow all new development must follow.

This is not a theoretical explanation. It is the architectural reference for the project.

---

# 1. Architectural Approach Summary

This project follows a layered and modular architecture on top of CodeIgniter 4.

Core stack:
- Route
- Filter
- Controller
- DTO
- Service
- Model / Repository
- View / Partial / Renderer

Core principle:
- Controllers stay thin
- DTOs shape and transfer validated data
- Services hold business rules
- Models handle data access
- Views are presentation-only
- Filters are the entry gate for auth and permissions

Summary flow:

Route → Filter → Controller → DTO → Service → Model / Repository → View / Partial

This structure must be preserved especially in critical areas such as auth, permissions, builders, orders, shipments, and digital access.

---

# 2. Layers and Responsibilities

## 2.1 Route Layer

The route layer is the entry point.

Responsibilities:
- URL → controller mapping
- route groups
- attaching auth / permission filters
- separating admin / secretary / user areas
- defining explicit entry points for modules

Rules:
- no business logic in routes
- critical modules must not be left unprotected
- route names and behavior must not be changed unnecessarily

---

## 2.2 Filter Layer

The filter layer is the security gate.

Responsibilities:
- authentication checks
- role / permission checks
- enforcing deny-by-default
- enforcing admin / secretary / user separation at first level
- providing route-level protection

Summary flow:

Request → Filter → Allow / Deny → Controller

Rules:
- access must not be opened by bypassing filters
- hiding menu items is never enough
- secretary access must always be protected at route level too
- for critical actions, filters must be supported by service-level guards

---

## 2.3 Controller Layer

The controller layer is the request/response bridge.

Responsibilities:
- receive request
- trigger validation flow where needed
- obtain auth context
- create DTOs or prepare data for DTOs
- call the relevant service
- return response or view

Rules:
- heavy business logic must not live inside controllers
- multi-step workflows must not be implemented in controllers
- authorization decisions must not be solved only inside controllers
- controllers are orchestration entry points, not business-rule centers

Summary flow:

Request → Controller → DTO → Service → Response / View

---

## 2.4 DTO Layer

The DTO layer carries validated and structured data.

Responsibilities:
- transport data between controller and service
- clarify input fields
- standardize data shape
- reduce unnecessary field passing for each use case

Example DTOs:
- CreateSkuDTO
- AddToCartDTO
- CheckoutDTO
- UpdateShipmentDTO
- CreateReviewDTO
- UpdateAccountDTO

Rules:
- raw scattered request data must not be passed directly to services
- DTOs should be defined per use case
- DTOs are a control point for validation and data shape
- DTOs carry data; they do not contain heavy business logic

---

## 2.5 Service Layer

The service layer is the center of business rules.

Responsibilities:
- product creation / update flows
- cart calculations
- checkout preparation
- order creation
- price snapshot logic
- inventory checks
- shipment status transitions
- review moderation flow
- digital access grant / revoke
- builder publish flow
- permission-sensitive domain logic

Rules:
- business logic that should not live in controllers belongs here
- the service layer is use-case oriented
- large uncontrolled services must not emerge
- repeated logic should be reused through shared methods whenever possible
- similar functions must not be duplicated in a way that bloats the service
- when needed, a new service may be introduced for a clearly separate responsibility area
- however, a new service must not be created for every small task
- a single service should preferably remain under roughly 700-800 lines
- if a service naturally grows beyond this range, responsibility-based splitting should be evaluated

Summary flow:

Controller → DTO → Service → Model / Repository

---

## 2.6 Model / Repository Layer

The model layer is the data access layer.

Responsibilities:
- CRUD operations
- queries
- relationship-based data fetching
- persistence
- supporting transaction-friendly access where needed

Rules:
- heavy workflow logic must not live in models
- models are not the center of business rules
- data correctness and access clarity must be preserved
- repository-style separation can be applied in a controlled way when needed

---

## 2.7 View / Partial / Renderer Layer

The view layer only produces presentation output.

Responsibilities:
- render HTML output
- render admin panel partials
- render storefront blocks
- use reusable partials in builder-based screens
- produce block-based output through PageRenderer or similar rendering flow

Rules:
- business logic must not grow inside views
- authorization must not rely only on view hiding
- view files must not become bloated with uncontrolled condition trees

---

# 3. High-Level System Areas

This project is not a single flat application. It contains multiple connected architectural areas:

- Auth & RBAC
- Admin Panel
- Secretary Panel / Permission-bound Operations
- User Frontend / Storefront
- Catalog Domain
- Cart / Checkout / Order
- Inventory / Stock
- Shipment / Shipping Operations
- Review / Favorites / Ratings / Questions
- Dashboard Builder
- Page Builder
- Digital Reader / Digital Access
- Audit Log / Traceability

These areas should be treated as distinct modules, while still obeying the same auth, permission, DTO, service, and rendering rules.

---

# 4. Auth and RBAC Architecture

Authentication and authorization are the backbone of the system.

Core data structures:
- users
- roles
- permissions
- role_permissions
- user_roles
- user_permissions

High-level flow:

Login Request
→ AuthService
→ UserRoleService / Permission Resolution
→ Session / Auth Context
→ Redirect by Role and Permission
→ Protected Route Access

Role model:
- admin
- secretary
- user

Permission model:
- role-based baseline permissions
- user-specific overrides for secretary
- route-level filters
- service-level guards for critical actions

Rules:
- deny-by-default must be preserved
- a secretary must never be assumed to be an admin
- URL-based authorization bypass must not be possible
- authorization must be preserved both at entry and action level

---

# 5. Admin Panel Architecture

The admin panel is the operational center of the project.

Core modules:
- dashboard
- dashboard builder
- page builder
- product management
- inventory management
- order management
- shipment management
- review moderation
- user / secretary management
- campaign / content areas
- email/sms management
- complaint management
- banner management
- payment reports, refunds
- customers, customer notes and messages
- product reviews
- sales reports, traffic analysis
- log records, notification management
- operational analytics and statistics screens

The admin panel architecture works in two main directions:

## 5.1 Management Modules

Flow:
Admin Route
→ Filter
→ Controller
→ DTO
→ Service
→ Model
→ Admin View

This covers classic management modules such as products, inventory, orders, shipments, moderation, and user management.

## 5.2 Managed Interface Modules

Flow:
Admin Builder Route
→ Filter
→ Builder Controller
→ Builder Service
→ Version / Block Models
→ Preview / Publish

Here the admin manages not only data, but also selected dashboard and storefront structures in a block-based way.

Note:
- statistical data, stock tracking, shipment tracking, and shipment optimization related areas are treated as data-driven operational modules on the admin side
- they are part of the operational management architecture rather than separate screen-level architectural concerns

---

# 6. Secretary Architecture

The secretary area is a subset of the admin side, but it is not equivalent to admin access.

Core approach:
- minimum access by default
- explicit permission grants
- route-level protection
- service-level critical checks
- auditable actions

Summary flow:

Secretary Route
→ Filter (permission-based)
→ Controller
→ DTO
→ Service
→ Model
→ Limited View

Examples of secretary-accessible areas may include:
- review moderation
- selected order operations
- shipment updates
- explicitly allowed operational screens

Rules:
- backend protection is mandatory for secretary access
- menu hiding alone is not considered access control
- actions such as moderation and shipment updates must be audit-friendly

---

# 7. User Frontend / Storefront Architecture

The user frontend is the customer experience layer.

Main screens:
- home
- product list
- product detail
- favorites
- my account
- my orders
- my reviews
- product questions
- seller questions
- digital books
- cart
- checkout

Additional customer actions:
- add to favorites
- write reviews
- rate purchased products
- read purchased digital books
- manage account information

General flow:

Frontend Route
→ Filter (auth / ownership where required)
→ Controller
→ DTO
→ Service
→ Model
→ Frontend View / Renderer

Rules:
- the frontend must not be treated as pure visual work
- data flow, pricing logic, inventory control, ownership, and access rules are part of the frontend architecture
- published version logic must be preserved on storefront pages integrated with the page builder

---

# 8. Catalog Domain Architecture

The product layer is not treated as a single flat table. It follows domain separation.

Core concepts:
- Work / Publication
- SKU
- Author
- Category
- Publisher
- Media / Cover / Gallery
- DigitalAsset
- Inventory

Goals:
- manage multiple sellable variants of the same work
- keep print / digital / bundle separation explicit
- apply inventory rules only to relevant SKUs
- build a stronger storefront and order layer

Summary flow:
Catalog Service
→ Work / SKU / Media Models
→ Product DTO / ViewModel
→ Product List / Product Detail Rendering

---

# 9. Cart, Checkout, and Order Architecture

This area is the commercial core of the project.

Core structures:
- carts
- cart_items
- orders
- order_items
- payments
- payment_attempts
- webhook_events

High-level checkout flow:

User Action
→ AddToCartDTO / CheckoutDTO
→ CartService
→ Price Recalculation
→ Stock Check
→ OrderService
→ Order Snapshot Creation
→ Payment Flow
→ Success / Failure Handling

Core rules:
- cart data is temporary but critical
- repricing may be required during checkout
- order history must be preserved through snapshots
- payment callback / webhook logic must be idempotent
- historical order data must not be mutated later in a truth-breaking way

---

# 10. Inventory / Stock Architecture

The inventory architecture is especially critical for printed products.

Core approach:
- inventory belongs to print SKUs
- in the MVP approach, stock may be reduced after payment success
- however, stock must still be rechecked during payment flow
- inventory changes are important from an audit perspective

Summary flow:

Cart / Checkout
→ Inventory Check
→ Payment Success
→ Inventory Update
→ Shipment Preparation

Additional areas:
- reorder level
- backorder policy
- stock-in history
- low-stock signals

---

# 11. Shipment / Shipping Architecture

Shipment is not just a single "shipped" field.

Core structures:
- shipments
- shipment_events
- tracking data
- shipment status transitions

Flow:

Order Paid
→ Fulfillment Pending
→ Shipment Creation
→ Shipment Events
→ Delivered / Returned

Core rules:
- shipment should be event-based
- status transitions must be controlled
- secretary or admin updates must remain audit-friendly
- customer-facing shipment views can rely on the event structure

Note:
Shipment tracking and shipment optimization are treated as part of the admin operational architecture.

---

# 12. Reviews, Favorites, Ratings, and Questions Architecture

Customer interaction is broader than product browsing.

Core modules:
- favorites / wishlist
- product reviews
- rating / stars
- product questions
- seller questions
- moderation actions
- product views

Example flow:
User Action
→ DTO
→ Interaction Service
→ Model
→ Moderation / Response Rendering

Review moderation flow:
User Review
→ Pending
→ Moderation Action
→ Approved / Hidden / Rejected

Rules:
- a secretary may moderate, but not with unrestricted admin power
- controlled hiding / soft delete may be preferred over hard delete
- rating should only be enabled under suitable conditions, especially purchase-linked rules should be enforced in the service layer

---

# 13. Dashboard Builder Architecture

The dashboard builder works with drag-and-drop and configurable blocks on the admin side.

Core structures:
- dashboards
- dashboard_versions
- dashboard_blocks / block_types
- dashboard_block_instances
- dashboard_data_sources
- config_json

Core features:
- block-based placement
- configuration-based display
- data-source binding
- visibility control
- size / order / layout settings
- drag-and-drop behavior
- preview / publish approach when needed
- cache support for performance

High-level flow:

Admin Builder UI
→ Drag & Drop Layout State
→ Builder DTO / Config
→ Dashboard Builder Service
→ Version / Block Persistence
→ Rendered Dashboard

This module is not just a visual editor. It is a manageable dashboard system driven by actual data sources.

---

# 14. Page Builder Architecture

The page builder makes the storefront manageable.

Core structures:
- pages
- page_versions
- block_types
- block_instances
- media_assets
- theme_tokens

Targeted pages:
- home
- product_list
- product_detail
- cart
- other storefront pages when needed later

Rendering flow:

Page Route
→ PageController / PageService
→ Published Page Version Resolution
→ Block Instances Ordered
→ DataSource Resolution
→ Partial Rendering
→ Final Page Output

Rules:
- draft / published / archived separation must remain intact
- bad config must not crash the system uncontrollably
- schema-based config validation must remain intact
- slot-based controlled flexibility is preferred for product card and product detail structures

---

# 15. Digital Reader and Digital Access Architecture

This area aims to make digital product consumption more secure.

Core approach:
- digital files are stored outside the webroot
- no direct public links
- token-based access
- purchase / ownership validation
- content displayed through a reader layer
- watermark, logging, and revoke logic supported

High-level flow:

Purchased Digital Product
→ Ownership Check
→ Token Generation
→ Reader Route
→ Access Validation
→ Stream / Render Preparation
→ Canvas-based Reader Output
→ Watermark / Logging

Initial version approach:
- full DRM is not the goal
- the goal is deterrence, access control, and traceability

Direction for future versions:
- stronger watermark strategies
- short-lived / renewable tokens
- stronger access logging
- device / session limitations
- additional protection layers that make digital copying harder

---

# 16. Audit Log and Traceability Architecture

Audit logging is not auxiliary in this project. It is a core module.

Core structures:
- audit_logs
- actor_user_id
- action_code
- entity_type
- entity_id
- before_json
- after_json
- ip
- user_agent
- created_at

Areas that must especially be logged:
- permission changes
- price changes
- stock changes
- review moderation
- order status transitions
- shipment status transitions
- page publish
- dashboard publish
- digital access revoke

Flow:
Critical Action
→ Service Layer
→ Domain Update
→ Audit Log Write
→ Response

---

# 17. State Machines and Workflow Rules

Some areas must not be treated as free-form string updates.

Core status areas:
- User.status
- SKU.status
- Order.status
- Payment.status
- Shipment.status
- DigitalAccess.status
- Review.status

Goals:
- prevent invalid transitions
- keep domain behavior rule-driven inside the service layer
- preserve consistency in order, payment, shipment, and moderation flows

---

# 18. Cross-Cutting Concerns

There are shared concerns that cut across many modules:

- auth
- permissions
- validation
- DTO shaping
- audit logging
- error handling
- encoding safety
- migration safety
- file upload safety
- rate limiting
- secure headers
- caching
- preview vs publish separation

These are architecture-wide rules, not module-local details.

---

# 19. Development and Extension Principle

New development should be evaluated in this order:

1. Where does it connect to the current flow?
2. Does it affect route / filter behavior?
3. Is a DTO needed?
4. Should the logic live in an existing service or a new one?
5. Is a model change required?
6. Does it affect a view / partial / renderer?
7. Does it affect audit or permissions?
8. Does it affect publish / versioning?
9. Is the solution future-friendly without being over-abstracted?

This system should grow into a professional architecture through controlled evolution, not through premature over-abstraction.

---

# 20. Architecture Summary

The core architectural rule of this project is:

- filters are the security gate
- controllers are the input/output layer
- DTOs are controlled data carriers
- services are the center of business rules
- models are the data access layer
- renderer/views are the presentation layer
- audit is the trace of critical actions
- builder systems provide controlled flexibility
- the storefront is not only a UI, but the visible face of domain flows
- the digital reader targets controlled access and deterrence, not absolute protection
