# Google Customer Reviews Integration for PrestaShop

This module provides a seamless integration of **Google Customer Reviews** for PrestaShop 1.7, 8.x, and 9.x. It automatically displays the Google opt-in survey on the order confirmation page.

## 📌 Features
- **Automatic Integration**: Injects the Google code only on the Success Page (Order Confirmation).
- **Dynamic Data**: Automatically pulls Order ID, Customer Email, and Country Code.
- **Product Reviews Support**: Automatically gathers and sends GTINs (EAN13/UPC) to Google for product ratings.
- **Multilingual**: The Google popup automatically adapts to the customer's browser language.
- **Modern Compatibility**: Fully compatible with PrestaShop 8/9 and PHP 8.1+.

## 🚀 Installation via ZIP

1. **Prepare the ZIP**: Ensure the folder inside the ZIP is named `googlecustomerreviews` and contains all the files.
2. **Upload**: 
   - Go to your PrestaShop Admin Panel.
   - Navigate to **Modules** > **Module Manager**.
   - Click the **Upload a module** button at the top right.
   - Select the `googlecustomerreviews.zip` file.
3. **Install**: Once uploaded, PrestaShop will show a success message. Click **Install** if it hasn't automatically started.
4. **Verification**: After installation, make sure the module is "Enabled".

## ⚙️ Configuration
The module uses your Merchant ID directly in the code for maximum performance.
To update your ID:
1. Open Config
2. `'merchant_id' => 'xxxxxxxx'`.

## 🧪 Testing
- Place a test order in your store.
- Upon reaching the "Thank you for your purchase" page, the Google Customer Reviews opt-in dialog should appear.
- **Note**: If the dialog does not appear, ensure that your browser's **AdBlocker** is disabled, as it may block Google's scripts.

## 🛠 Technical Requirements
- **<!DOCTYPE HTML>** must be present at the top of your store's HTML (standard in PrestaShop).
- The checkout confirmation must occur on your own domain.

## ⚖️ License
This module is free to use and modify (MIT License).
