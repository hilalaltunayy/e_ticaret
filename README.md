# E-Commerce Platform

## Demo Preview

- [Demo 1](./demo6.mp4)  
- [Demo 2](./demo7.mp4)  
- [Demo 3](./demo8.mp4)  

---

## Overview

This project is a modular and secure e-commerce platform designed to support both digital (PDF/EPUB) and physical book sales.

The system includes a dynamically configurable Admin Dashboard, a Page Builder, and a robust Role-Based Access Control (RBAC) architecture.

It is designed not only as a working application but also as a scalable and maintainable system architecture.

---

## Project Goals

- Clear separation of Admin / Secretary / Customer roles  
- Deny-by-default RBAC architecture  
- Secure digital content delivery  
- Dynamic Dashboard and Page Builder  
- Layered architecture (Controller → Service → Model)  
- Secure payment, order, and stock management  
- Full system traceability with audit logs  

---

## System Architecture

### General Flow
HTTP Request
↓
Filter (Auth + RBAC + CSRF + Security Headers)
↓
Controller (Thin Layer)
↓
Service (Business Logic)
↓
Repository / Model
↓
Database


### Layers

| Layer        | Responsibility |
|-------------|---------------|
| Filter      | Authentication and permission enforcement |
| Controller  | Request/response orchestration |
| Service     | Business logic |
| DTO         | Typed data transfer |
| Model       | Database interaction |
| Migration   | Schema versioning |

---

## RBAC (Role-Based Access Control)

### Roles

- ADMIN  
- SECRETARY  
- CUSTOMER  

### Structure

- roles  
- permissions  
- role_permissions  
- user_roles  
- user_permissions (override)  

### Key Principles

- Deny-by-default access control  
- Route-level authorization enforcement  
- Secondary validation in Service layer  
- Admin-controlled permission overrides  
- Minimal privilege assignment for secretary role  

---

## Dashboard Builder

The admin can dynamically build dashboards using modular blocks.

### Block Types

- Stat Card  
- Chart (Bar / Pie / Line)  
- Slider  
- Quote  
- Calendar  

### Data Sources

- sales_by_category  
- top_authors  
- visitors  
- favorites_count  

### Features

- Draft / Published / Archived versions  
- Cached rendering (no recalculation on each load)  

---

## Page Builder

The admin can construct pages such as:

- Home  
- Category Listing  
- Product Detail  
- Cart  

### Design Approach

Instead of free HTML:

- predefined slots are used  
- controlled rendering structure  

### Benefits

- Reduced XSS risk  
- Easier maintenance  
- UI consistency  

---

## Domain Model

### Work → SKU Separation

- Work: content identity  
- SKU: sellable variant (PRINT / DIGITAL / BUNDLE)  

### Snapshot Policy

Each order item stores:

- unit_price_snapshot  
- product_name_snapshot  
- tax_snapshot  
- discount_snapshot  

Ensures historical consistency even if data changes later.

---

## Digital Content Security

### Approach

Not full DRM, but deterrence and traceability.

### Implementation

- Files stored outside webroot  
- Token-based access (/reader/{token})  
- PDF.js canvas rendering  
- Disabled text selection and printing  
- Watermark (email, order number, date)  
- Rate limiting  
- Access logging  

---

## Cart and Stock Management

### Cart

- cart  
- cart_items  
- cart_promotions  

Checkout includes re-pricing validation.

### Stock

- Stock reduced after payment  
- Re-checked before payment  
- Protected with transactions  

---

## Shipment System

### Entities

- shipments  
- shipment_events  

### Flow
PENDING → PREPARING → SHIPPED → DELIVERED

---

## Payment Flow
INITIATED → PENDING → SUCCESS / FAILED → REFUNDED

### Webhook Handling

- Idempotent processing  
- provider_txn_id unique constraint  
- webhook_events tracking  

---

## State Machines

### Order
PENDING_PAYMENT → PAID → FULFILLMENT_PENDING → SHIPPED → DELIVERED
### Digital Access
ACTIVE → EXPIRED → REVOKED


---

## Security Design

- Route-level access control  
- Service-level authorization checks  
- CSRF protection  
- XSS prevention via encoding and schema validation  
- Secure headers (HSTS, CSP, etc.)  
- Session security (HttpOnly, Secure, SameSite)  
- Secure file upload handling  
- Environment-based secret management  

---

## Audit Logging

### Structure

- actor_user_id  
- action_code  
- entity_type  
- entity_id  
- before_json  
- after_json  
- ip  

### Logged Events

- Permission changes  
- Product updates  
- Layout publishing  
- Comment deletion  
- Digital access revocation  
- Shipment status updates  

---

## Edge Case Handling

| Scenario | Solution |
|--------|---------|
| Price changes | Checkout re-pricing |
| Stock race conditions | Transaction + validation |
| Duplicate webhooks | Idempotent handlers |
| Unauthorized access attempts | Route guard |
| Builder misconfiguration | JSON schema validation |
| Digital content abuse | Watermark + rate limiting |

---

## Testing Strategy

- Service layer unit tests  
- Policy validation tests  
- State machine tests  
- Permission matrix tests  
- Order snapshot tests  
- Idempotency tests  

---

## Migration Strategy

- Versioned migrations  
- Seeder for default roles and permissions  
- Controlled production migration  
- Rollback support  

---

## Design Decisions

- RBAC designed from the beginning  
- Business logic isolated in Service layer  
- Snapshot model ensures data integrity  
- Slot-based UI reduces XSS risk  
- Audit logs provide full traceability  
- Builder versioning enables rollback  
- Token-based access provides controlled digital delivery  

---

## Scope Limitations

- Native mobile application  
- AI recommendation system  
- Marketplace integrations  
- Multi-currency support  

---

## Conclusion

This project is not just a working e-commerce system, but a security-focused, modular, and scalable architecture built for long-term maintainability.

---

## Author

Hilal Yeşim Altunay
