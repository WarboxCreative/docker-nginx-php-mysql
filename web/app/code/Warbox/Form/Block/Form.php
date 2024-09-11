<?php declare(strict_types=1);

namespace Warbox\Form\Block;

use Magento\Framework\App\Area;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Warbox\Catalogue\Helper\Data as CatalogueHelper;

/**
 * Class Form
 * @package Warbox\Form\Block
 */
class Form extends Template
{
    protected FormKey $formKey;
    protected TransportBuilder $transportBuilder;
    protected StateInterface $state;
    protected StoreManagerInterface $storeManager;
    protected CatalogueHelper $helper;

    /**
     * Constructor
     *
     * @param Context               $context
     * @param FormKey               $formKey
     * @param TransportBuilder      $transportBuilder
     * @param StateInterface        $state
     * @param StoreManagerInterface $storeManager
     * @param CatalogueHelper       $helper
     * @param array                 $data
     */
    public function __construct(
        Template\Context $context,
        FormKey $formKey,
        TransportBuilder $transportBuilder,
        StateInterface $state,
        StoreManagerInterface $storeManager,
        CatalogueHelper $helper,
        array $data = []
    ) {
        $this->formKey          = $formKey;
        $this->transportBuilder = $transportBuilder;
        $this->state            = $state;
        $this->storeManager     = $storeManager;
        $this->helper           = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        $image = $this->getViewFileUrl('images/icons/Brochure@2x.png');
        return $this->helper->getCatalogueImg() !== 'media/catalogue/' ? $this->helper->getCatalogueImg() : $image;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        $default = 'Request a <span>FREE</span> 22/23 catalogue';
        return $this->helper->getCatalogueTitle() ? $this->helper->getCatalogueTitle() : $default;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getFormAction(string $type): string
    {
        return match ($type) {
            'distributor' => $this->getUrl('forms/submit/distributor', ['_secure' => true]),
            'supplier'    => $this->getUrl('forms/submit/supplier', ['_secure' => true]),
            'catalogue'   => $this->getUrl('forms/submit/catalogue', ['_secure' => true]),
            default       => '',
        };
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
     * @throws NoSuchEntityException
     * @throws MailException
     * @throws LocalizedException
     */
    public function getMailDetails(
        string $template,
        array $vars,
        string $mailTo = '',
        string $fromName = '',
        string $mailFrom = '',
    ): TransportInterface
    {
        if ($fromName === '') {
            $fromName = $this->_scopeConfig->getValue('trans_email/ident_sales/name', ScopeInterface::SCOPE_STORE);
        }
        if ($mailFrom === '') {
            $mailFrom = $this->_scopeConfig->getValue('trans_email/ident_sales/email', ScopeInterface::SCOPE_STORE);
        }
        if ($mailTo === '') {
            $mailTo = $this->_scopeConfig->getValue('trans_email/ident_sales/email', ScopeInterface::SCOPE_STORE);
        }

        $storeId = $this->storeManager->getStore()->getId();
        $from    = ['email' => $mailFrom, 'name' => $fromName];

        $this->inlineTranslation->suspend();

        $storeScope      = ScopeInterface::SCOPE_STORE;
        $templateOptions = [
            'area'  => Area::AREA_FRONTEND,
            'store' => $storeId
        ];

        $transport = $this->transportBuilder->setTemplateIdentifier($template)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($vars)
            ->setFromByScope($from, $storeScope)
            ->addTo($mailTo)
            ->getTransport();

        $this->inlineTranslation->resume();

        return $transport;
    }
}
