# Codilar_WishList — Module Documentation

## Overview

`Codilar_WishList` is a custom Magento 2 REST API module that extends the native wishlist functionality by exposing five customer-scoped REST endpoints. All endpoints require a valid customer bearer token (JWT). The module is built on top of `Magento_Wishlist`, `Magento_Customer`, `Magento_Catalog`, and `Magento_Quote`.

---

## Module Identity

| Field       | Value                        |
|-------------|------------------------------|
| Module name | `Codilar_WishList`           |
| Composer    | `codilar/wishlist`           |
| Type        | `magento2-module`            |
| Namespace   | `Codilar\WishList`           |
| Location    | `app/code/Codilar/WishList/` |

---

## Module Dependencies (load sequence)

```xml
<sequence>
    <module name="Magento_Wishlist"/>
    <module name="Magento_Customer"/>
    <module name="Magento_Catalog"/>
    <module name="Magento_Quote"/>
</sequence>
```

---

## Directory Structure

```
Codilar/WishList/
├── Api/
│   ├── WishlistManagementInterface.php       # Service contract (5 methods)
│   └── Data/
│       ├── ActionResponseInterface.php       # Response shape for add/remove/move
│       ├── WishlistItemInterface.php         # Shape of a single wishlist item
│       └── WishlistResponseInterface.php     # Response shape for getWishlist
├── Model/
│   ├── WishlistManagement.php                # Central facade + DataObject implementation
│   ├── AddToWishlist.php                     # Task: add product to wishlist
│   ├── RemoveFromWishlist.php                # Task: remove product from wishlist
│   ├── GetWishlist.php                       # Task: retrieve wishlist items
│   ├── MoveToCart.php                        # Task: move wishlist item → cart
│   └── MoveToWishlist.php                    # Task: move cart item → wishlist
├── etc/
│   ├── module.xml                            # Module declaration & sequence
│   ├── di.xml                                # Preference bindings
│   └── webapi.xml                            # REST route definitions
├── composer.json
└── registration.php
```

---

## Architecture

### Design Pattern

The module uses a **Facade + Task Object** pattern:

- `WishlistManagement` acts as the single facade that implements all three API data interfaces and delegates each operation to a dedicated task class.
- Each task class (`AddToWishlist`, `RemoveFromWishlist`, etc.) has a single public `execute()` method and owns its own dependencies.
- All interfaces are bound to `WishlistManagement` via `di.xml` preferences, so Magento's object manager resolves them to the same concrete class.

### Dependency Injection Preferences (`di.xml`)

| Interface                                        | Concrete Class                          |
|--------------------------------------------------|-----------------------------------------|
| `WishlistManagementInterface`                    | `Model\WishlistManagement`              |
| `Data\WishlistItemInterface`                     | `Model\WishlistManagement`              |
| `Data\WishlistResponseInterface`                 | `Model\WishlistManagement`              |
| `Data\ActionResponseInterface`                   | `Model\WishlistManagement`              |

> All four interfaces resolve to `WishlistManagement`, which extends `Magento\Framework\DataObject` and uses its `getData`/`setData` key-value store to satisfy all getter/setter contracts.

---

## REST API Endpoints

All routes are customer-scoped (`<resource ref="self"/>`), meaning the caller must authenticate with a customer token.

| Method | URL                        | Operation              | Request Body              | Response Type              |
|--------|----------------------------|------------------------|---------------------------|----------------------------|
| POST   | `/V1/wishlist/add`         | Add to wishlist        | `{ "productSku": "..." }` | `ActionResponseInterface`  |
| POST   | `/V1/wishlist/remove`      | Remove from wishlist   | `{ "productSku": "..." }` | `ActionResponseInterface`  |
| GET    | `/V1/wishlist/get`         | Get wishlist           | _(none)_                  | `WishlistResponseInterface`|
| POST   | `/V1/wishlist/movetocart`  | Move to cart           | `{ "productSku": "..." }` | `ActionResponseInterface`  |
| POST   | `/V1/wishlist/movetowishlist` | Move to wishlist    | `{ "cartItemId": 123 }`   | `ActionResponseInterface`  |

---

## Data Contracts

### `ActionResponseInterface`

Returned by add, remove, moveToCart, and moveToWishlist.

| Field        | Type   | Description                                      |
|--------------|--------|--------------------------------------------------|
| `success`    | bool   | Whether the operation succeeded                  |
| `message`    | string | Human-readable result or error message           |
| `product_id` | int    | Magento product entity ID                        |
| `sku`        | string | Product SKU                                      |
| `name`       | string | Product name                                     |
| `quote_id`   | int    | Active cart ID (populated by move operations)    |

### `WishlistResponseInterface`

Returned by getWishlist.

| Field          | Type                      | Description                        |
|----------------|---------------------------|------------------------------------|
| `success`      | bool                      | Whether the fetch succeeded        |
| `message`      | string                    | Status message                     |
| `customer_id`  | int                       | Authenticated customer ID          |
| `total_items`  | int                       | Count of items in the wishlist     |
| `items`        | WishlistItemInterface[]   | Array of wishlist item objects     |

### `WishlistItemInterface`

Each element inside `items` of `WishlistResponseInterface`.

| Field         | Type   | Description                              |
|---------------|--------|------------------------------------------|
| `item_id`     | int    | Wishlist item entity ID                  |
| `product_id`  | int    | Magento product entity ID                |
| `name`        | string | Product name                             |
| `sku`         | string | Product SKU                              |
| `price`       | float  | Regular price                            |
| `final_price` | float  | Final price (after discounts)            |
| `added_at`    | string | Datetime the item was added (ISO string) |

---

## Task Classes — Detailed Breakdown

### 1. `AddToWishlist`

**File:** `Model/AddToWishlist.php`  
**Triggered by:** `POST /V1/wishlist/add`  
**Input:** `string $productSku`

**Flow:**
1. Resolve `customerId` from `UserContextInterface`. Return failure if unauthenticated.
2. Load product by SKU via `ProductRepositoryInterface`.
3. Load or create the customer's wishlist via `WishlistFactory::loadByCustomerId($customerId, true)`.
4. Build a store ID array from all available stores.
5. Query `Item\CollectionFactory` filtered by wishlist + stores + `product_id` to check for duplicates.
6. If duplicate found → return `success=false` with message `"Product already exists in wishlist"`.
7. Otherwise call `$wishlist->addNewItem($product)` and `$wishlist->save()`.
8. Return `success=true` with product details.

**Dependencies:**

| Dependency                  | Purpose                                  |
|-----------------------------|------------------------------------------|
| `WishlistFactory`           | Load/create customer wishlist            |
| `Item\CollectionFactory`    | Duplicate check query                    |
| `ProductRepositoryInterface`| Resolve SKU → product entity             |
| `StoreManagerInterface`     | Collect all store IDs for filter         |
| `UserContextInterface`      | Get authenticated customer ID            |
| `ObjectManagerInterface`    | Instantiate `ActionResponseInterface`    |

---

### 2. `RemoveFromWishlist`

**File:** `Model/RemoveFromWishlist.php`  
**Triggered by:** `POST /V1/wishlist/remove`  
**Input:** `string $productSku`

**Flow:**
1. Resolve `customerId`. Return failure if unauthenticated.
2. Load wishlist. Return failure if wishlist has no ID.
3. Load product by SKU.
4. Query item collection filtered by wishlist + stores + `product_id`.
5. Get first matching item. Return failure if not found.
6. Call `$item->delete()` and `$wishlist->save()`.
7. Return `success=true` with product details.

**Dependencies:** Same as `AddToWishlist`.

---

### 3. `GetWishlist`

**File:** `Model/GetWishlist.php`  
**Triggered by:** `GET /V1/wishlist/get`  
**Input:** _(none)_

**Flow:**
1. Resolve `customerId`. Return failure if unauthenticated.
2. Load wishlist. If no wishlist ID, return `success=true` with empty items.
3. Query item collection filtered by wishlist + all store IDs.
4. For each item:
   - Create a new `WishlistItemInterface` instance via object manager.
   - Set `item_id`, `product_id`, `added_at`.
   - Load product by ID for the current store to get `name`, `sku`, `price`, `final_price`.
   - On product load failure, set empty/zero values gracefully.
5. Return `WishlistResponseInterface` with `customer_id`, `total_items`, and `items[]`.

**Dependencies:**

| Dependency                  | Purpose                                      |
|-----------------------------|----------------------------------------------|
| `WishlistFactory`           | Load customer wishlist                       |
| `Item\CollectionFactory`    | Fetch all wishlist items                     |
| `ProductRepositoryInterface`| Enrich each item with product data           |
| `StoreManagerInterface`     | Store-scoped product loading + store IDs     |
| `UserContextInterface`      | Get authenticated customer ID                |
| `ObjectManagerInterface`    | Instantiate response and item objects        |

---

### 4. `MoveToCart`

**File:** `Model/MoveToCart.php`  
**Triggered by:** `POST /V1/wishlist/movetocart`  
**Input:** `string $productSku`

**Flow:**
1. Resolve `customerId`. Return failure if unauthenticated.
2. Load wishlist. Return failure if not found.
3. Load product by SKU.
4. Query wishlist item collection for the product. Return failure if not found.
5. Try to load the customer's active cart via `CartRepositoryInterface::getActiveForCustomer()`.
   - If no active cart exists, create one via `CartManagementInterface::createEmptyCartForCustomer()`.
6. Add product to cart: `$quote->addProduct($product, new DataObject(['qty' => 1]))`.
7. Save the cart.
8. Delete the wishlist item and save the wishlist.
9. Return `success=true` with product details and `quote_id`.

**Dependencies:**

| Dependency                  | Purpose                                      |
|-----------------------------|----------------------------------------------|
| `WishlistFactory`           | Load customer wishlist                       |
| `Item\CollectionFactory`    | Find wishlist item by product                |
| `ProductRepositoryInterface`| Resolve SKU → product entity                 |
| `CartManagementInterface`   | Create empty cart if none exists             |
| `CartRepositoryInterface`   | Load/save active cart                        |
| `StoreManagerInterface`     | Collect store IDs for item filter            |
| `UserContextInterface`      | Get authenticated customer ID                |
| `ObjectManagerInterface`    | Instantiate response + `DataObject` for qty  |

---

### 5. `MoveToWishlist`

**File:** `Model/MoveToWishlist.php`  
**Triggered by:** `POST /V1/wishlist/movetowishlist`  
**Input:** `int $cartItemId`

**Flow:**
1. Resolve `customerId`. Return failure if unauthenticated.
2. Load active cart. Return failure if no active cart.
3. Iterate `$quote->getAllItems()` to find the item matching `$cartItemId`. Return failure if not found.
4. Load product by `$cartItem->getProductId()`.
5. Load or create customer wishlist.
6. Check if product already exists in wishlist:
   - If yes: remove item from cart, save cart, return `success=true` with message `"Product already in wishlist; removed from cart"`.
7. If not in wishlist: call `$wishlist->addNewItem($product)` and save.
8. Remove item from cart and save cart.
9. Return `success=true` with product details and `quote_id`.

**Dependencies:**

| Dependency                          | Purpose                                      |
|-------------------------------------|----------------------------------------------|
| `WishlistFactory`                   | Load/create customer wishlist                |
| `WishlistItemCollectionFactory`     | Duplicate check in wishlist                  |
| `CartRepositoryInterface`           | Load/save active cart                        |
| `ProductRepositoryInterface`        | Load product from cart item's product ID     |
| `StoreManagerInterface`             | Collect store IDs for wishlist filter        |
| `UserContextInterface`              | Get authenticated customer ID                |
| `ObjectManagerInterface`            | Instantiate `ActionResponseInterface`        |

---

## Authentication

All endpoints use `<resource ref="self"/>`, which means:

- The caller must pass a **customer bearer token** in the `Authorization` header.
- The token is resolved to a `customerId` internally via `UserContextInterface::getUserId()`.
- If no valid token is provided, all tasks return `success=false` with `"Customer not authenticated"`.

**Example header:**
```
Authorization: Bearer <customer_token>
```

---

## Error Handling

All task classes wrap their logic in a `try/catch (\Exception $e)` block. On any unhandled exception, the response is:

```json
{
  "success": false,
  "message": "<exception message>",
  "product_id": 0,
  "sku": "",
  "name": "",
  "quote_id": 0
}
```

`GetWishlist` additionally handles per-item product load failures gracefully (sets empty/zero values) so a single bad product does not break the entire list response.

---

## Sample API Usage

### Add to Wishlist
```http
POST /rest/V1/wishlist/add
Authorization: Bearer <customer_token>
Content-Type: application/json

{ "productSku": "WS12-XS-Orange" }
```

**Response:**
```json
{
  "success": true,
  "message": "Product added to wishlist successfully",
  "product_id": 67,
  "sku": "WS12-XS-Orange",
  "name": "Radiant Tee",
  "quote_id": 0
}
```

---

### Get Wishlist
```http
GET /rest/V1/wishlist/get
Authorization: Bearer <customer_token>
```

**Response:**
```json
{
  "success": true,
  "message": "",
  "customer_id": 5,
  "total_items": 1,
  "items": [
    {
      "item_id": 12,
      "product_id": 67,
      "name": "Radiant Tee",
      "sku": "WS12-XS-Orange",
      "price": 22.00,
      "final_price": 22.00,
      "added_at": "2024-01-15 10:30:00"
    }
  ]
}
```

---

### Move to Cart
```http
POST /rest/V1/wishlist/movetocart
Authorization: Bearer <customer_token>
Content-Type: application/json

{ "productSku": "WS12-XS-Orange" }
```

**Response:**
```json
{
  "success": true,
  "message": "Product moved to cart successfully",
  "product_id": 67,
  "sku": "WS12-XS-Orange",
  "name": "Radiant Tee",
  "quote_id": 42
}
```

---

### Move to Wishlist
```http
POST /rest/V1/wishlist/movetowishlist
Authorization: Bearer <customer_token>
Content-Type: application/json

{ "cartItemId": 101 }
```

**Response:**
```json
{
  "success": true,
  "message": "Product moved to wishlist successfully",
  "product_id": 67,
  "sku": "WS12-XS-Orange",
  "name": "Radiant Tee",
  "quote_id": 42
}
```

---

## Installation

```bash
php bin/magento module:enable Codilar_WishList
php bin/magento setup:upgrade
php bin/magento cache:flush
```

---

## Known Limitations / Notes

- The module uses `ObjectManagerInterface` directly inside task classes for response instantiation. This is a deviation from Magento best practices (factory injection is preferred) but works because all interfaces are bound to `WishlistManagement` via `di.xml`.
- `MoveToCart` adds products with a hardcoded `qty` of `1`. Configurable/bundle products are not explicitly handled and may require `buyRequest` options.
- There is no pagination support on `GET /V1/wishlist/get`; all items are returned in a single response.
- The module does not create its own database tables; it relies entirely on `Magento_Wishlist` tables (`wishlist`, `wishlist_item`).
