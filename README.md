# Google Customer Reviews Integration for PrestaShop

A professional and lightweight module to integrate **Google Customer Reviews** (Opt-in and Badge) into PrestaShop 1.7, 8.x, and 9.x.

## 📌 Features
- **Opt-in Survey**: Automatically shows the Google survey invitation on the order confirmation page.
- **Merchant Badge**: Display your Google Seller Rating badge on every page.
- **Customizable Delivery**: Set the number of days to wait before Google sends the survey email (configurable in admin).
- **Manual Language Control**: Force the survey language (HU, RO, EN) regardless of browser settings.
- **Badge Positioning**: Choose between Bottom Right, Bottom Left, or Disabled.
- **Modern Standards**: Fully compatible with PrestaShop 8 & 9 and PHP 8.1+.

## 🚀 Installation via ZIP

1. **Prepare the ZIP**: Create a zip file named `googlecustomerreviews.zip`. The internal structure must be:
   - `googlecustomerreviews/googlecustomerreviews.php`
   - `googlecustomerreviews/logo.png`
   - `googlecustomerreviews/README.md`
   - `googlecustomerreviews/views/templates/hook/order-confirmation.tpl`
   - `googlecustomerreviews/views/templates/hook/badge.tpl`
2. **Upload**: 
   - Login to PrestaShop Admin > **Modules** > **Module Manager**.
   - Click **Upload a module** and select your `.zip` file.
3. **Configure**: 
   - Click the **Configure** button on the module.
   - Enter your **Google Merchant ID**.
   - Set the **Estimated Delivery (Days)** (Default is 5).
   - Select the **Survey Language** and **Badge Position**.
   - Click **Save**.

## ⚙️ How it Works
1. **Opt-in Dialog**: When a customer completes a purchase, the Google Opt-in dialog appears.
2. **Delayed Survey**: If the customer clicks "Yes", Google records the request.
3. **Email Invitation**: Google sends the actual survey email **only after** the delivery days you set (e.g., 5 days later).
4. **Merchant Badge**: Shows your store's rating. If you have no ratings yet, it will display "No rating available".

## 🧪 Testing & Troubleshooting
- **AdBlockers**: Ensure your browser's AdBlocker is turned off, otherwise, the Google script will not load.
- **Merchant Center**: You must enable "Customer Reviews" in your Google Merchant Center account (Growth > Manage Programs).
- **Console Check**: If the window doesn't appear, press `F12` and check the "Console" tab for errors.

## 🛠 Technical Details
- **Author**: markoo
- **Version**: 1.5.0
- **Requirement**: Your theme must include `<!DOCTYPE HTML>` (standard in PrestaShop).

## ⚖️ License
This module is free to use and modify (MIT License).
