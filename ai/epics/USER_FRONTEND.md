# Epic: User Frontend Completion

## Goal
Complete all core shopping, account, and interaction flows on the user side.

This epic represents the customer-facing side of the project and is the most critical part of Version 1.

---

## Scope

### 1. Product Discovery
- product list page
- category-based listing
- filtering and sorting
- pagination / infinite scroll

### 2. Product Detail
- product detail page
- product information (name, description, price, etc.)
- stock information
- same-category recommendations
- "you may also like" section

### 3. User Account System
- registration
- login
- logout
- account update

### 4. Favorites
- add to favorites
- remove from favorites
- favorites page

### 5. Cart and Checkout
- add to cart
- update cart
- remove from cart
- checkout flow
- initiate payment

### 6. Orders
- create order
- order history
- order detail view

### 7. User Interactions
- product reviews
- rating system
- product questions
- seller questions

### 8. Digital Books
- access purchased digital content
- reading screen (reader integration)
- user-specific access control

---

## Rules

- unauthenticated users cannot perform certain actions
- favorites and cart are user-based
- rating requires purchase
- ownership validation is mandatory
- DTO + Service architecture must be used

---

## Success Criteria

- user can log in
- user can browse products
- user can add favorites
- user can create cart
- user can place orders
- user can access digital content

---

## Notes

- project cannot be considered v1 complete without this epic
- backend flow is as important as UI
