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
        $this->version = '1.5.0';
        $this->author = 'markoo';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Google Customer Reviews');
        $this->description = $this->l('Complete Google Reviews integration with Badge, Opt-in and Delivery delay settings.');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('displayFooter') &&
            Configuration::updateValue('GCR_MERCHANT_ID', '') &&
            Configuration::updateValue('GCR_LANGUAGE', 'hu') &&
            Configuration::updateValue('GCR_BADGE_POS', 'BOTTOM_RIGHT') &&
            Configuration::updateValue('GCR_DELIVERY_DAYS', 5);
    }

    public function uninstall()
    {
        return parent::uninstall() && 
            Configuration::deleteByName('GCR_MERCHANT_ID') &&
            Configuration::deleteByName('GCR_LANGUAGE') &&
            Configuration::deleteByName('GCR_BADGE_POS') &&
            Configuration::deleteByName('GCR_DELIVERY_DAYS');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submit' . $this->name)) {
            Configuration::updateValue('GCR_MERCHANT_ID', strval(Tools::getValue('GCR_MERCHANT_ID')));
            Configuration::updateValue('GCR_LANGUAGE', strval(Tools::getValue('GCR_LANGUAGE')));
            Configuration::updateValue('GCR_BADGE_POS', strval(Tools::getValue('GCR_BADGE_POS')));
            Configuration::updateValue('GCR_DELIVERY_DAYS', (int)Tools::getValue('GCR_DELIVERY_DAYS'));
            $output .= $this->displayConfirmation($this->l('Settings updated successfully'));
        }
        return $output . $this->renderForm();
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
            ['id' => 'USER_DEFINED', 'name' => 'Disabled'],
        ];

        $form = [
            'form' => [
                'legend' => ['title' => $this->l('Settings'), 'icon' => 'icon-cogs'],
                'input' => [
                    ['type' => 'text', 'label' => $this->l('Merchant ID'), 'name' => 'GCR_MERCHANT_ID', 'required' => true],
                    [
                        'type' => 'text', 
                        'label' => $this->l('Estimated Delivery (Days)'), 
                        'name' => 'GCR_DELIVERY_DAYS', 
                        'desc' => $this->l('Number of days after order to send the survey email. Default: 5'),
                        'size' => 5
                    ],
                    ['type' => 'select', 'label' => $this->l('Survey Language'), 'name' => 'GCR_LANGUAGE', 'options' => ['query' => $lang_options, 'id' => 'id', 'name' => 'name']],
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
        $helper->fields_value['GCR_DELIVERY_DAYS'] = Configuration::get('GCR_DELIVERY_DAYS');
        $helper->fields_value['GCR_LANGUAGE'] = Configuration::get('GCR_LANGUAGE');
        $helper->fields_value['GCR_BADGE_POS'] = Configuration::get('GCR_BADGE_POS');

        return $helper->generateForm([$form]);
    }

    public function hookDisplayFooter($params)
    {
        $merchant_id = Configuration::get('GCR_MERCHANT_ID');
        if (empty($merchant_id)) return '';

        $this->context->smarty->assign([
            'merchant_id' => $merchant_id,
            'badge_pos' => Configuration::get('GCR_BADGE_POS'),
        ]);
        return $this->display(__FILE__, 'views/templates/hook/badge.tpl');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $order = $params['order'] ?? null;
        if (!Validate::isLoadedObject($order)) return '';

        $merchant_id = Configuration::get('GCR_MERCHANT_ID');
        if (empty($merchant_id)) return '';

        $days = (int)Configuration::get('GCR_DELIVERY_DAYS');
        $delivery_date = date('Y-m-d', strtotime('+' . $days . ' days'));

        $customer = new Customer((int)$order->id_customer);
        $address = new Address((int)$order->id_address_delivery);
        $country = new Country((int)$address->id_country);

        $this->context->smarty->assign([
            'merchant_id' => $merchant_id,
            'gcr_lang' => Configuration::get('GCR_LANGUAGE'),
            'order_id' => (string)$order->id,
            'email' => $customer->email,
            'country_code' => $country->iso_code,
            'delivery_date' => $delivery_date,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/order-confirmation.tpl');
    }
}
