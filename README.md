# Google Customer Reviews Integration for PrestaShop

This module provides a professional integration of **Google Customer Reviews** for PrestaShop 1.7, 8.x, and 9.x. It handles the opt-in survey invitation on the order confirmation page with customizable settings.

## 📌 Features
- **Manual Language Selection**: Force the survey language (HU, RO, EN) via the admin panel.
- **Admin Configuration**: Set your Merchant ID and Language without touching the code.
- **Product Reviews Support**: Automatically sends product GTINs (EAN13/UPC) to Google for product ratings.
- **Modern Compatibility**: Optimized for PrestaShop 8 & 9 and PHP 8.1+.
- **Lightweight**: Zero impact on database performance.

## 🚀 Installation via ZIP

1. **Prepare the ZIP**: Create a zip file containing the folder `googlecustomerreviews`. The structure must be:
   - `googlecustomerreviews/googlecustomerreviews.php`
   - `googlecustomerreviews/logo.png`
   - `googlecustomerreviews/README.md`
   - `googlecustomerreviews/views/templates/hook/order-confirmation.tpl`
2. **Upload**: 
   - Go to PrestaShop Admin > **Modules** > **Module Manager**.
   - Click **Upload a module** and select your `.zip` file.
3. **Configure**: 
   - Once installed, click the **Configure** button.
   - Enter your **Google Merchant ID**.
   - Select your preferred **Survey Language** from the drop-down menu.
   - Click **Save**.

## ⚙️ How the Opt-in Process Works (Important!)
When a customer completes a purchase:
1. The Google Opt-in dialog appears.
2. If the customer clicks **"Yes, I want to participate"**, the window simply closes. 
3. **Note**: No new window or survey will open immediately. 
4. Google will record the request and send an email survey to the customer only **after the estimated delivery date** (configured in the code as +5 days after the order).

## 🧪 Testing & Troubleshooting
- **AdBlockers**: Ensure your browser's AdBlocker is turned off, otherwise, the Google script will not load.
- **Merchant Center**: Ensure that "Customer Reviews" is enabled in your **Google Merchant Center** (Growth > Manage Programs).
- **Console Check**: If the window doesn't appear, press `F12` in your browser and check the "Console" tab for any Google-related errors.

## 🛠 Technical Requirements
- Your store must have `<!DOCTYPE HTML>` at the top of the page.
- The confirmation page must be on your own domain.

## ⚖️ License
This module is free to use and modify (MIT License).
