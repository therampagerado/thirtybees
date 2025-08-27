<?php
/**
 * 2007-2016 PrestaShop
 *
 * thirty bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017-2024 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.thirtybees.com for more information.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017-2024 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

/**
 * Class PasswordControllerCore
 */
class PasswordControllerCore extends FrontController
{

    /** @var string $php_self */
    public $php_self = 'password';

    /** @var bool $auth */
    public $auth = false;

    /** @var bool|Customer $customer */
    protected $customer;

    /**
     * Start forms process
     *
     * @see FrontController::postProcess()
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        if (Tools::isSubmit('email')) {
            if (!($email = trim(Tools::getValue('email'))) || !Validate::isEmail($email)) {
                $this->errors[] = Tools::displayError('Invalid email address.');
            } else {
                $customer = new Customer();
                $customer->getByemail($email);
                if (Validate::isLoadedObject($customer) && $customer->active) {
                    if ((strtotime($customer->last_passwd_gen.'+'.($minTime = (int) Configuration::get('PS_PASSWD_TIME_FRONT')).' minutes') - time()) > 0) {
                        // enforce delay between password regenerations
                    } else {
                        $token = bin2hex(random_bytes(32));
                        $ttl = (int) Configuration::get('PS_PASSWD_RESET_TOKEN_TTL');
                        if ($ttl <= 0) {
                            $ttl = 60;
                        }
                        $customer->reset_password_token = $token;
                        $customer->reset_password_validity = date('Y-m-d H:i:s', time() + $ttl * 60);
                        $customer->update();
                        $url = $this->context->link->getPageLink('password', true, null, 'token='.$token);
                        $mailParams = [
                            '{email}'     => $customer->email,
                            '{lastname}'  => $customer->lastname,
                            '{firstname}' => $customer->firstname,
                            '{url}'       => $url,
                            '{token_validity}' => (int) ceil($ttl / 60),
                        ];
                        if (!Mail::Send($this->context->language->id, 'password_query', Mail::l('Password query confirmation'), $mailParams, $customer->email, $customer->firstname.' '.$customer->lastname)) {
                            $this->errors[] = Tools::displayError('An error occurred while sending the email.');
                        }
                        PrestaShopLogger::addLog(sprintf('Password reset token issued for %s from %s [%s]', $customer->email, Tools::getRemoteAddr(), isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''), 1, null, 'Customer', (int) $customer->id);
                    }
                }
                $this->context->smarty->assign([
                    'confirmation' => 2,
                    'customer_email' => $email
                ]);
            }
            if ($this->ajax) {
                $return = [
                    'hasError' => !empty($this->errors),
                    'errors'   => $this->errors,
                ];
                $this->ajaxDie(json_encode($return));
            }
        } elseif ($customer = $this->getCustomer()) {
            if ((strtotime($customer->last_passwd_gen.'+'.(int) Configuration::get('PS_PASSWD_TIME_FRONT').' minutes') - time()) > 0) {
                Tools::redirect('index.php?controller=authentication&error_regen_pwd');
            } else {
                $password = Tools::getValue('password');
                $confirm = Tools::getValue('confirm_password');
                if ($password) {
                    if (! Validate::isPasswd($password)) {
                        $this->errors[] = Tools::displayError('This password does not meet security criteria');
                    } elseif ($password != $confirm) {
                        $this->errors[] = Tools::displayError('Password does not match value from confirmation field');
                    } else {
                        $this->setNewPassword($customer, $password);
                    }
                }
            }
        }
    }

    /**
     * Assign template vars related to page content
     *
     * @see FrontController::initContent()
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();
        if ($customer = $this->getCustomer()) {
            $this->context->smarty->assign(['customer' => $customer]);
            $this->setTemplate(_PS_THEME_DIR_.'password-set.tpl');
        } else {
            $this->setTemplate(_PS_THEME_DIR_.'password.tpl');
        }
    }

    /**
     * Method that to set new password
     *
     * @param Customer $customer
     * @param string $password
     * @throws PrestaShopException
     */
    public function setNewPassword(Customer $customer, $password)
    {
        $customer->passwd = Tools::hash($password);
        $customer->last_passwd_gen = date('Y-m-d H:i:s', time());
        if ($customer->update()) {
            $customer->clearResetPasswordToken();
            Hook::triggerEvent('actionPasswordRenew', [
                'customer' => $customer,
                'password' => $password
            ]);
            $this->context->smarty->assign(['confirmation' => 1]);
        } else {
            $this->errors[] = Tools::displayError('An error occurred with your account, which prevents us from sending you a new password. Please report this issue using the contact form.');
        }
    }

    /**
     * Returns customer that requested password reset, if possible
     *
     * @return bool|Customer
     */
    protected function getCustomer()
    {
        if (is_null($this->customer)) {
            try {
                $this->customer = static::resolveCustomer();
            } catch (PrestaShopException $e) {
                $this->customer = false;
                $this->errors[] = $e->getMessage();
            }
        }
        return $this->customer;
    }

    /**
     * Resolves customer from request parameters
     *
     * @return bool|Customer
     *
     * @throws PrestaShopException
     */
    protected static function resolveCustomer()
    {
        $token = Tools::getValue('token');
        if ($token) {
            $customer = Customer::getByResetPasswordToken($token);
            if ($customer && $customer->active) {
                return $customer;
            }
            throw new PrestaShopException(Tools::displayError('We cannot regenerate your password with the data you\'ve submitted.'));
        }
        return false;
    }

}
