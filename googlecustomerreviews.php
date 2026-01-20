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
        $this->version = '1.2.0';
        $this->author = 'markoo';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true; // Fontos az admin felület kinézetéhez

        parent::__construct();

        $this->displayName = $this->l('Google Customer Reviews');
        $this->description = $this->l('Google Customer Reviews integration for PrestaShop 8 and 9 systems.');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayOrderConfirmation') &&
            Configuration::updateValue('GCR_MERCHANT_ID', '534853117');
    }

    public function uninstall()
    {
        return parent::uninstall() && Configuration::deleteByName('GCR_MERCHANT_ID');
    }

    // Admin felület létrehozása (Configure gomb)
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $merchant_id = strval(Tools::getValue('GCR_MERCHANT_ID'));

            if (!$merchant_id || empty($merchant_id) || !Validate::isGenericName($merchant_id)) {
                $output .= $this->displayError($this->l('Invalid Merchant ID'));
            } else {
                Configuration::updateValue('GCR_MERCHANT_ID', $merchant_id);
                $output .= $this->displayConfirmation($this->l('Settings updated successfully'));
            }
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['GCR_MERCHANT_ID'] = Configuration::get('GCR_MERCHANT_ID');

        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Google Merchant ID'),
                        'name' => 'GCR_MERCHANT_ID',
                        'size' => 20,
                        'required' => true,
                        'desc' => $this->l('Enter your 9-digit Google Merchant Center ID.'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        return $helper->generateForm([$form]);
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $order = $params['order'] ?? null;
        if (!Validate::isLoadedObject($order)) return '';

        $merchant_id = Configuration::get('GCR_MERCHANT_ID');
        if (!$merchant_id) return '';

        $customer = new Customer((int)$order->id_customer);
        $address = new Address((int)$order->id_address_delivery);
        $country = new Country((int)$address->id_country);

        $products = $order->getProducts();
        $gtins = [];
        foreach ($products as $product) {
            if (!empty($product['ean13'])) $gtins[] = ['gtin' => $product['ean13']];
            elseif (!empty($product['upc'])) $gtins[] = ['gtin' => $product['upc']];
        }

        $this->context->smarty->assign([
            'merchant_id' => $merchant_id,
            'order_id' => (string)$order->id,
            'email' => $customer->email,
            'country_code' => $country->iso_code,
            'delivery_date' => date('Y-m-d', strtotime('+5 days')),
            'products_gtin' => json_encode($gtins),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/order-confirmation.tpl');
    }
}
