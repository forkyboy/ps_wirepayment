<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * @since 1.5.0
 *
 * @property Ps_Wirepayment $module
 */
class Ps_WirepaymentBackgroundModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] === 'ps_wirepayment') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            exit($this->module->getTranslator()->trans('This payment method is not available.', [], 'Modules.Wirepayment.Shop'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

        $owner = Configuration::get('BANK_WIRE_OWNER');
        $details = nl2br(Configuration::get('BANK_WIRE_DETAILS') ?: '');
        $address = nl2br(Configuration::get('BANK_WIRE_ADDRESS') ?: '');

        $this->context->smarty->assign([
            'bankwire_owner' => $owner,
            'bankwire_details' => $details,
            'bankwire_address' => $address,
            'total_to_pay' => $this->context->getCurrentLocale()->formatPrice($total, $currency->iso_code),
            'background_title' => $this->getTranslator()->trans('Bank transfer instructions', [], 'Modules.Wirepayment.Shop'),
            'background_intro' => $this->getTranslator()->trans('Please use the following details to complete your bank transfer. We are finalizing your order in the background and will redirect you shortly.', [], 'Modules.Wirepayment.Shop'),
            'processing_message' => $this->getTranslator()->trans('We are confirming your order. This usually takes just a moment…', [], 'Modules.Wirepayment.Shop'),
            'error_message' => $this->getTranslator()->trans('We were unable to finalize your order automatically. Please refresh the page to try again or contact us if the issue persists.', [], 'Modules.Wirepayment.Shop'),
            'async_process_url' => $this->context->link->getModuleLink('ps_wirepayment', 'validation', [
                'ajax' => 1,
                'id_cart' => $cart->id,
                'key' => $customer->secure_key,
            ], true),
        ]);

        $this->setTemplate('module:ps_wirepayment/views/templates/front/background_execution.tpl');
    }
}
