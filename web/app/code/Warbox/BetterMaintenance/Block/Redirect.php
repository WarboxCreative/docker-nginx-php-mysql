<?php declare(strict_types=1);

namespace Warbox\BetterMaintenance\Block;

use Magento\Cms\Model\Page as CmsPage;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\BetterMaintenance\Helper\Data as HelperData;
use Mageplaza\BetterMaintenance\Model\Config\Source\System\RedirectTo;

/**
 * Class Redirect
 * @package Warbox\BetterMaintenance\Block
 */
class Redirect extends \Mageplaza\BetterMaintenance\Block\Redirect
{
    /**
     * Redirect constructor.
     *
     * @param Context    $context
     * @param HelperData $helperData
     * @param CmsPage    $cmsPage
     * @param Http       $response
     * @param array      $data
     */
    public function __construct(
        Template\Context $context,
        HelperData $helperData,
        CmsPage $cmsPage,
        Http $response,
        array $data = []
    ) {
        parent::__construct($context, $helperData, $cmsPage, $response, $data);
    }

    /**
     * @return array[]|false|string[]
     */
    public function getWhiteListPage()
    {
        if (!$this->_helperData->getWhitelistPage()) {
            return [];
        }

        return preg_split("/(\r\n|\n|\r)/", $this->_helperData->getWhitelistPage());
    }

    /**
     * @return array
     */
    public function getWhiteListIp(): array
    {
        if (!$this->_helperData->getWhitelistIp()) {
            return [];
        }

        return $this->_helperData->getWhitelistIp();
    }

    /**
     * @return bool|Http|HttpInterface
     */
    public function redirectToUrl()
    {
        if (!$this->_helperData->isEnabled()) {
            return false;
        }

        $endTime = $this->_helperData->getEndTime() ?: '';
        if (strtotime($this->_localeDate->date()->format('m/d/Y H:i:s')) >= strtotime($endTime)) {
            return false;
        }

        $this->_response->setNoCacheHeaders();
        $redirectTo = $this->_helperData->getConfigGeneral('redirect_to');
        $currentUrl = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $currentIp  = $this->_helperData->getClientIp();

        foreach ($this->getWhitelistIp() as $value) {
            if ($this->_helperData->checkIp($currentIp, trim($value))) {
                return false;
            }
        }

        foreach ($this->getWhiteListPage() as $value) {
            if ($currentUrl === $value) {
                return false;
            }
        }

        if ($redirectTo === RedirectTo::MAINTENANCE_PAGE || $redirectTo === RedirectTo::COMING_SOON_PAGE) {
            return false;
        }

        $route = $redirectTo;

        if ($this->_cmsPage->getIdentifier() === $redirectTo) {
            return false;
        }

        $url = $this->getUrl($route);

        return $this->_response->setRedirect($url);
    }
}
