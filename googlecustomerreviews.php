<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class GoogleCustomerReviews extends Module
{
    public function __construct()
    {
        $this->name = 'googlecustomerreviews';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'markoo';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Google Customer Reviews');
        $this->description = $this->l('Customer Reviews integration for PrestaShop 8 and 9 systems.');
    }

    public function install()
    {
        return parent::install() && $this->registerHook('displayOrderConfirmation');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        // Az "order" objektum kinyerése a paraméterekből
        $order = $params['order'] ?? null;
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        $customer = new Customer((int)$order->id_customer);
        $address = new Address((int)$order->id_address_delivery);
        $country = new Country((int)$address->id_country);

        // Termékek és GTIN kódok lekérése
        $products = $order->getProducts();
        $gtins = [];
        foreach ($products as $product) {
            if (!empty($product['ean13'])) {
                $gtins[] = ['gtin' => $product['ean13']];
            } elseif (!empty($product['upc'])) {
                $gtins[] = ['gtin' => $product['upc']];
            }
        }

        $this->context->smarty->assign([
            'merchant_id' => '534853117',
            'order_id' => (string)$order->id,
            'email' => $customer->email,
            'country_code' => $country->iso_code,
            'delivery_date' => date('Y-m-d', strtotime('+5 days')),
            'products_gtin' => json_encode($gtins),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/order-confirmation.tpl');
    }
}
