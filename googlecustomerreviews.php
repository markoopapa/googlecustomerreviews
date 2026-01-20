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
        $this->version = '1.5.1';
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
            // Csak akkor adjunk alapértelmezett értéket, ha még nem létezik
            Configuration::updateValue('GCR_MERCHANT_ID', Configuration::get('GCR_MERCHANT_ID') ? Configuration::get('GCR_MERCHANT_ID') : '') &&
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
            // Értékek lekérése és mentése
            $merchant_id = strval(Tools::getValue('GCR_MERCHANT_ID'));
            $language = strval(Tools::getValue('GCR_LANGUAGE'));
            $badge_pos = strval(Tools::getValue('GCR_BADGE_POS'));
            $delivery_days = (int)Tools::getValue('GCR_DELIVERY_DAYS');

            Configuration::updateValue('GCR_MERCHANT_ID', $merchant_id);
            Configuration::updateValue('GCR_LANGUAGE', $language);
            Configuration::updateValue('GCR_BADGE_POS', $badge_pos);
            Configuration::updateValue('GCR_DELIVERY_DAYS', $delivery_days);

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

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Merchant ID'),
                        'name' => 'GCR_MERCHANT_ID',
                        'required' => true,
                        'desc' => $this->l('Your 9-digit Google Merchant Center ID.')
                    ],
                    [
                        'type' => 'text', 
                        'label' => $this->l('Estimated Delivery (Days)'), 
                        'name' => 'GCR_DELIVERY_DAYS', 
                        'desc' => $this->l('Number of days after order to send the survey email. Default: 5'),
                    ],
                    [
                        'type' => 'select', 
                        'label' => $this->l('Survey Language'), 
                        'name' => 'GCR_LANGUAGE', 
                        'options' => ['query' => $lang_options, 'id' => 'id', 'name' => 'name']
                    ],
                    [
                        'type' => 'select', 
                        'label' => $this->l('Badge Position'), 
                        'name' => 'GCR_BADGE_POS', 
                        'options' => ['query' => $pos_options, 'id' => 'id', 'name' => 'name']
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_callbacks = true;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        // Itt töltjük be a mentett értékeket az adatbázisból
        $helper->fields_value['GCR_MERCHANT_ID'] = Configuration::get('GCR_MERCHANT_ID');
        $helper->fields_value['GCR_LANGUAGE'] = Configuration::get('GCR_LANGUAGE');
        $helper->fields_value['GCR_BADGE_POS'] = Configuration::get('GCR_BADGE_POS');
        $helper->fields_value['GCR_DELIVERY_DAYS'] = Configuration::get('GCR_DELIVERY_DAYS') ? Configuration::get('GCR_DELIVERY_DAYS') : 5;

        return $helper->generateForm([$fields_form]);
    }

    public function hookDisplayFooter($params)
    {
        $merchant_id = Configuration::get('GCR_MERCHANT_ID');
        if (empty($merchant_id)) {
            return '';
        }

        $this->context->smarty->assign([
            'merchant_id' => $merchant_id,
            'badge_pos' => Configuration::get('GCR_BADGE_POS'),
        ]);
        return $this->display(__FILE__, 'views/templates/hook/badge.tpl');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $order = $params['order'] ?? null;
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        $merchant_id = Configuration::get('GCR_MERCHANT_ID');
        if (empty($merchant_id)) {
            return '';
        }

        $days = (int)Configuration::get('GCR_DELIVERY_DAYS');
        $delivery_date = date('Y-m-d', strtotime('+' . $days . ' days'));

        $this->context->smarty->assign([
            'merchant_id' => $merchant_id,
            'gcr_lang' => Configuration::get('GCR_LANGUAGE'),
            'order_id' => (string)$order->id,
            'email' => (new Customer((int)$order->id_customer))->email,
            'country_code' => (new Country((int)(new Address((int)$order->id_address_delivery))->id_country))->iso_code,
            'delivery_date' => $delivery_date,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/order-confirmation.tpl');
    }
}
