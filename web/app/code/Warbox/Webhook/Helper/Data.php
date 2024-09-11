<?php declare(strict_types=1);

namespace Warbox\Webhook\Helper;

use Exception;
use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Webhook\Block\Adminhtml\LiquidFilters;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\Config\Source\Status;
use Mageplaza\Webhook\Model\HistoryFactory;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Model\ResourceModel\Hook\Collection;

/**
 * Class Data
 *
 * @package Warbox\Webhook\Helper
 */
class Data extends \Mageplaza\Webhook\Helper\Data
{
    /**
     * Data constructor.
     *
     * @param Context                     $context
     * @param ObjectManagerInterface      $objectManager
     * @param StoreManagerInterface       $storeManager
     * @param UrlInterface                $backendUrl
     * @param TransportBuilder            $transportBuilder
     * @param CurlFactory                 $curlFactory
     * @param LiquidFilters               $liquidFilters
     * @param HookFactory                 $hookFactory
     * @param HistoryFactory              $historyFactory
     * @param CustomerRepositoryInterface $customer
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        UrlInterface $backendUrl,
        TransportBuilder $transportBuilder,
        CurlFactory $curlFactory,
        LiquidFilters $liquidFilters,
        HookFactory $hookFactory,
        HistoryFactory $historyFactory,
        CustomerRepositoryInterface $customer
    ) {
        parent::__construct(
            $context,
            $objectManager,
            $storeManager,
            $backendUrl,
            $transportBuilder,
            $curlFactory,
            $liquidFilters,
            $hookFactory,
            $historyFactory,
            $customer
        );
    }

    public function send($item, $hookType)
    {
        if (!$this->isEnabled()) {
            return;
        }

        /** @var Collection $hookCollection */
        $hookCollection = $this->hookFactory->create()->getCollection()
            ->addFieldToFilter('hook_type', $hookType)
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('store_ids', [
                ['finset' => Store::DEFAULT_STORE_ID],
                ['finset' => $this->getItemStore($item)]
            ])
            ->setOrder('priority', 'ASC');

        $isSendMail = $this->getConfigGeneral('alert_enabled');
        $sendTo     = $this->getConfigGeneral('send_to') ?: '';
        $sendTo     = explode(',', $sendTo);

        foreach ($hookCollection as $hook) {
            if ($hook->getHookType() === HookType::ORDER) {
                $statusItem  = $item->getStatus();
                $orderStatus = explode(',', $hook->getOrderStatus());
                if (!in_array($statusItem, $orderStatus, true)) {
                    continue;
                }
            }
            $history = $this->historyFactory->create();
            $data    = [
                'hook_id'     => $hook->getId(),
                'hook_name'   => $hook->getName(),
                'store_ids'   => $hook->getStoreIds(),
                'hook_type'   => $hook->getHookType(),
                'priority'    => $hook->getPriority(),
                'payload_url' => $this->generateLiquidTemplate($item, $hook->getPayloadUrl()),
                'body'        => $this->generateLiquidTemplate($item, $hook->getBody())
            ];
            $history->addData($data);
            try {
                $result = $this->sendHttpRequestFromHook($hook, $item);
                $history->setResponse(isset($result['response']) ? $result['response'] : '');
            } catch (Exception $e) {
                $result = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
            if ($result['success'] === true) {
                $history->setStatus(Status::SUCCESS);
            } else {
                $history->setStatus(Status::ERROR)
                    ->setMessage($result['message']);
                if ($isSendMail) {
                    $this->sendMail(
                        $sendTo,
                        __('Something went wrong while sending %1 hook', $hook->getName()),
                        $this->getConfigGeneral('email_template'),
                        $this->getStoreId()
                    );
                }
            }

            $history->save();
        }
    }
}
