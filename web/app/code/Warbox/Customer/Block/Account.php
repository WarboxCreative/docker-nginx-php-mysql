<?php declare(strict_types=1);

namespace Warbox\Customer\Block;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Form;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Account
 * @package Warbox\Customer\Block
 */
class Account extends Template
{
    protected HttpContext $httpContext;
    protected FormKey $formKey;

    /**
     * Constructor
     *
     * @param Context     $context
     * @param HttpContext $httpContext
     * @param FormKey     $formKey
     * @param array       $data
     */
    public function __construct(
        Template\Context $context,
        HttpContext $httpContext,
        FormKey $formKey,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->formKey     = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function customerLoggedIn(): bool
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * @return string
     */
    public function getCustomerAjaxLoginUrl(): string
    {
        return $this->getUrl('customer/ajax/login');
    }

    /**
     * @return string
     */
    public function getCustomerLoginUrl(): string
    {
        return $this->getUrl('customer/account/loginPost/');
    }

    /**
     * @return string
     */
    public function getCustomerAjaxLogoutUrl(): string
    {
        return $this->getUrl('customer/ajax/logout');
    }

    /**
     * @return string
     */
    public function getCustomerLogoutUrl(): string
    {
        return $this->getUrl('customer/account/logout');
    }

    /**
     * Return base url.
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseUrl(): string
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get customer register url
     *
     * @return string
     */
    public function getCustomerRegisterUrl(): string
    {
        return $this->getUrl('customer/account/login');
    }

    /**
     * Get customer forgot password url
     *
     * @return string
     */
    public function getCustomerForgotPasswordUrl(): string
    {
        return $this->getUrl('customer/account/forgotpassword');
    }

    /**
     * Is autocomplete enabled for storefront
     *
     * @return string
     */
    private function isAutocompleteEnabled(): string
    {
        return $this->_scopeConfig->getValue(
            Form::XML_PATH_ENABLE_AUTOCOMPLETE,
            ScopeInterface::SCOPE_STORE
        ) ? 'on' : 'off';
    }
}
