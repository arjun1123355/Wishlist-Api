# Codilar_WishList — REST API Module

A simple Magento 2 custom module that exposes Wishlist management via REST APIs.

---

## Module Structure

```
app/code/Codilar/WishList/
├── Api/
│   └── WishlistManagementInterface.php   ← API contract
├── Model/
│   └── WishlistManagement.php            ← Business logic
├── etc/
│   ├── module.xml                        ← Module declaration
│   ├── webapi.xml                        ← REST route definitions
│   └── di.xml                            ← Interface → Model binding
├── registration.php                      ← Module registration
└── README.md
```

---

## Installation

```bash
php bin/magento module:enable Codilar_WishList
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## Disable

```bash
php bin/magento module:disable Codilar_WishList
php bin/magento setup:upgrade
php bin/magento cache:flush
```

---

## Authentication

All endpoints require a **customer token**. Get one first:

**POST** `{{base_url}}/rest/V1/integration/customer/token`

```json
{
  "username": "customer@example.com",
  "password": "yourpassword"
}
```

Use the returned token as a Bearer token in all subsequent requests:

```
Authorization: Bearer <token>
Content-Type: application/json
```

---

## REST API Endpoints

### 1. Add Product to Wishlist

| | |
|---|---|
| **Method** | POST |
| **URL** | `/rest/V1/wishlist/add` |

**Request Body:**
```json
{
  "productId": 1,
  "qty": 1
}
```

**Response:**
```json
"Product \"Simple Product\" added to wishlist."
```

---

### 2. Remove Product from Wishlist

| | |
|---|---|
| **Method** | DELETE |
| **URL** | `/rest/V1/wishlist/remove/{itemId}` |

**URL Param:** `itemId` — the wishlist item ID (from Get Wishlist response)

**Response:**
```json
"Item 5 removed from wishlist."
```

---

### 3. Get Wishlist Products

| | |
|---|---|
| **Method** | GET |
| **URL** | `/rest/V1/wishlist` |

**Response:**
```json
[
  {
    "item_id": 5,
    "product_id": 1,
    "sku": "simple-product",
    "name": "Simple Product",
    "price": 29.99,
    "qty": 1.0,
    "added_at": "2024-01-01 10:00:00"
  }
]
```

---

### 4. Move Product from Wishlist to Cart

| | |
|---|---|
| **Method** | POST |
| **URL** | `/rest/V1/wishlist/move-to-cart/{itemId}` |

**URL Param:** `itemId` — the wishlist item ID

**Response:**
```json
"Item moved to cart successfully."
```

---

### 5. Move Product from Cart to Wishlist

| | |
|---|---|
| **Method** | POST |
| **URL** | `/rest/V1/wishlist/move-to-wishlist/{itemId}` |

**URL Param:** `itemId` — the **quote item ID** from the cart

**Response:**
```json
"Item moved to wishlist successfully."
```

---

### 6. Fetch Wishlist Products (Detailed)

| | |
|---|---|
| **Method** | GET |
| **URL** | `/rest/V1/wishlist/fetch` |

**Response:**
```json
{
  "wishlist_id": 3,
  "customer_id": 1,
  "total_items": 2,
  "items": [
    {
      "item_id": 5,
      "product_id": 1,
      "sku": "simple-product",
      "name": "Simple Product",
      "price": 29.99,
      "qty": 1.0,
      "added_at": "2024-01-01 10:00:00"
    }
  ]
}
```

---

## Postman Setup

1. Create a new Postman Collection named **Codilar WishList APIs**
2. Add a Collection Variable: `base_url` = `http://your-magento-domain.com`
3. Add a Collection Variable: `token` = *(leave empty, set after login)*

**Login request (run first):**
- POST `{{base_url}}/rest/V1/integration/customer/token`
- In the **Tests** tab add:
  ```js
  pm.collectionVariables.set("token", pm.response.json());
  ```

**For all other requests, set Authorization:**
- Type: `Bearer Token`
- Token: `{{token}}`

---

## Error Responses

| Scenario | HTTP Code | Message |
|---|---|---|
| Not logged in | 401 | Customer is not logged in. |
| Item not found | 404 | Wishlist item X not found. |
| Product not found | 404 | No such entity with id = X |
