# Codilar AddCart - Cart Restriction Module

## Overview
This module restricts the "Add to Cart" functionality based on customer type and product price.

## Features
- Restrict add to cart for Guest users and General customer group
- Configure minimum product price from admin panel
- Custom error message with dynamic minimum amount

## Installation

1. Enable the module:
```bash
php bin/magento module:enable Codilar_AddCart
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## Configuration

Go to: **Stores → Configuration → Sales → Cart Restriction**

### Settings:
1. **Enable Feature**: Yes/No - Enable or disable the restriction
2. **Minimum Product Amount**: Set minimum price (e.g., 100)
3. **Error Message**: Custom message (use {{amount}} for dynamic value)

## How It Works

### Restriction Applied To:
- Guest users (not logged in)
- Logged-in users in "General" customer group (Group ID = 1)

### Restriction NOT Applied To:
- Customers in other groups (Wholesale, Retailer, etc.)
- When feature is disabled

### Example:
If minimum amount = 100:
- Product price $50 → Cannot add to cart (shows error)
- Product price $150 → Can add to cart

## Files Created:
- `registration.php` - Module registration
- `etc/module.xml` - Module declaration
- `etc/config.xml` - Default configuration
- `etc/di.xml` - Plugin registration
- `etc/adminhtml/system.xml` - Admin configuration
- `Helper/Data.php` - Configuration helper
- `Plugin/CartPlugin.php` - Main restriction logic
