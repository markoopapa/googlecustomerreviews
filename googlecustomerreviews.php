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
        $this->version = '1.4.0';
        $this->author = 'YourName';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Google Customer Reviews');
        $this->description = $this->l('Google Reviews integration with Badge support.');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('displayFooter') && // Új hook a jelvénynek
            Configuration::updateValue('GCR_MERCHANT_ID', '534853117') &&
            Configuration::updateValue('GCR_LANGUAGE', 'hu') &&
            Configuration::updateValue('GCR_BADGE_POS', 'BOTTOM_RIGHT');
    }

    public function uninstall()
    {
        return parent::uninstall() && 
            Configuration::deleteByName('GCR_MERCHANT_ID') &&
            Configuration::deleteByName('GCR_LANGUAGE') &&
            Configuration::deleteByName('GCR_BADGE_POS');
    }

    public function getContent()
    {
        if (Tools::isSubmit('submit' . $this->name)) {
            Configuration::updateValue('GCR_MERCHANT_ID', strval(Tools::getValue('GCR_MERCHANT_ID')));
            Configuration::updateValue('GCR_LANGUAGE', strval(Tools::getValue('GCR_LANGUAGE')));
            Configuration::updateValue('GCR_BADGE_POS', strval(Tools::getValue('GCR_BADGE_POS')));
            return $this->displayConfirmation($this->l('Settings updated successfully'));
        }
        return $this->renderForm();
    }

    protected function renderForm()
    {
        $lang_options = [
            ['id' => 'hu', 'name' => 'Hungarian (HU)'],
            ['id' => 'ro', 'name' => 'Romanian (RO)'],
            ['id' => 'en', 'name' => 'English (EN)'],
        ];

        $pos_options = [
            ['id' => 'BOTTOM_RIGHT', 'name' => 'Bottom Right'],
            ['id' => 'BOTTOM_LEFT', 'name' => 'Bottom Left'],
            ['id' => 'USER_DEFINED', 'name' => 'Disabled / Manual'],
        ];

        $form = [
            'form' => [
                'legend' => ['title' => $this->l('Settings'), 'icon' => 'icon-cogs'],
                'input' => [
                    ['type' => 'text', 'label' => $this->l('Merchant ID'), 'name' => 'GCR_MERCHANT_ID', 'required' => true],
                    ['type' => 'select', 'label' => $this->l('Language'), 'name' => 'GCR_LANGUAGE', 'options' => ['query' => $lang_options, 'id' => 'id', 'name' => 'name']],
                    ['type' => 'select', 'label' => $this->l('Badge Position'), 'name' => 'GCR_BADGE_POS', 'options' => ['query' => $pos_options, 'id' => 'id', 'name' => 'name']],
                ],
                'submit' => ['title' => $this->l('Save'), 'class' => 'btn btn-default pull-right'],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->fields_value['GCR_MERCHANT_ID'] = Configuration::get('GCR_MERCHANT_ID');
        $helper->fields_value['GCR_LANGUAGE'] = Configuration::get('GCR_LANGUAGE');
        $helper->fields_value['GCR_BADGE_POS'] = Configuration::get('GCR_BADGE_POS');

        return $helper->generateForm([$form]);
    }

    // A JELVÉNY MEGJELENÍTÉSE MINDEN OLDALON
    public function hookDisplayFooter($params)
    {
        $this->context->smarty->assign([
            'merchant_id' => Configuration::get('GCR_MERCHANT_ID'),
            'badge_pos' => Configuration::get('GCR_BADGE_POS'),
        ]);
        return $this->display(__FILE__, 'views/templates/hook/badge.tpl');
    }

    // AZ OPT-IN A VISSZAIGAZOLÓ OLDALON
    public function hookDisplayOrderConfirmation($params)
    {
        $order = $params['order'] ?? null;
        if (!Validate::isLoadedObject($order)) return '';

        $this->context->smarty->assign([
            'merchant_id' => Configuration::get('GCR_MERCHANT_ID'),
            'gcr_lang' => Configuration::get('GCR_LANGUAGE'),
            'order_id' => (string)$order->id,
            'email' => (new Customer((int)$order->id_customer))->email,
            'country_code' => (new Country((int)(new Address((int)$order->id_address_delivery))->id_country))->iso_code,
            'delivery_date' => date('Y-m-d', strtotime('+5 days')),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/order-confirmation.tpl');
    }
}
