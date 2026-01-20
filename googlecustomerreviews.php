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
        $this->version = '1.3.1';
        $this->author = 'markoo';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Google Customer Reviews');
        $this->description = $this->l('Google Reviews integration with manual language selection.');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayOrderConfirmation') &&
            Configuration::updateValue('GCR_MERCHANT_ID', '534853117') &&
            Configuration::updateValue('GCR_LANGUAGE', 'hu');
    }

    public function uninstall()
    {
        return parent::uninstall() && 
            Configuration::deleteByName('GCR_MERCHANT_ID') &&
            Configuration::deleteByName('GCR_LANGUAGE');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submit' . $this->name)) {
            Configuration::updateValue('GCR_MERCHANT_ID', strval(Tools::getValue('GCR_MERCHANT_ID')));
            Configuration::updateValue('GCR_LANGUAGE', strval(Tools::getValue('GCR_LANGUAGE')));
            $output .= $this->displayConfirmation($this->l('Settings updated successfully'));
        }
        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        // Itt definiáljuk a legördülő menü (Drop-down) tartalmát
        $options = [
            ['id' => 'hu', 'name' => 'Hungarian (Magyar)'],
            ['id' => 'ro', 'name' => 'Romanian (Română)'],
            ['id' => 'en', 'name' => 'English (Angol)'],
        ];

        $form = [
            'form' => [
                'legend' => ['title' => $this->l('Settings'), 'icon' => 'icon-cogs'],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Google Merchant ID'),
                        'name' => 'GCR_MERCHANT_ID',
                        'required' => true,
                    ],
                    [
                        'type' => 'select', // Legördülő menü típus
                        'label' => $this->l('Survey Language'),
                        'name' => 'GCR_LANGUAGE',
                        'options' => [
                            'query' => $options,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Select the language for the Google survey popup.'),
                    ],
                ],
                'submit' => ['title' => $this->l('Save'), 'class' => 'btn btn-default pull-right'],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->fields_value['GCR_MERCHANT_ID'] = Configuration::get('GCR_MERCHANT_ID');
        $helper->fields_value['GCR_LANGUAGE'] = Configuration::get('GCR_LANGUAGE');

        return $helper->generateForm([$form]);
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $order = $params['order'] ?? null;
        if (!Validate::isLoadedObject($order)) return '';

        $this->context->smarty->assign([
            'merchant_id' => Configuration::get('GCR_MERCHANT_ID'),
            'gcr_lang' => Configuration::get('GCR_LANGUAGE'), // Átadjuk a mentett nyelvet
            'order_id' => (string)$order->id,
            'email' => (new Customer((int)$order->id_customer))->email,
            'country_code' => (new Country((int)(new Address((int)$order->id_address_delivery))->id_country))->iso_code,
            'delivery_date' => date('Y-m-d', strtotime('+5 days')),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/order-confirmation.tpl');
    }
}
