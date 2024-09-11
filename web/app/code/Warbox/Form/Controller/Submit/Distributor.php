<?php declare(strict_types=1);

namespace Warbox\Form\Controller\Submit;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Warbox\Form\Block\Form;

/**
 * Class Distributor
 * @package Warbox\Form\Controller\Submit
 */
class Distributor extends Action implements HttpPostActionInterface
{
    private const PAGE_URL = '/become-a-distributor';
    private const EMAIL_TEMPLATE = 'become-a-distributor';

    protected Validator $validator;
    protected Form $helper;
    protected RequestInterface $request;
    protected $resultRedirectFactory;

    /**
     * @param Context               $context
     * @param Validator             $validator
     * @param Form                  $helper
     * @param ManagerInterface      $messageManager
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param RequestInterface      $request
     */
    public function __construct(
        Context $context,
        Validator $validator,
        Form $helper,
        ManagerInterface $messageManager,
        ResultRedirectFactory $resultRedirectFactory,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->validator             = $validator;
        $this->helper                = $helper;
        $this->messageManager        = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->request               = $request;
    }

    public function execute()
    {
        if (!$this->validator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage('Invalid form key.');
            return $this->resultRedirectFactory->create()
                ->setUrl(self::PAGE_URL);
        }

        $resultRedirect = $this->resultRedirectFactory->create()->setUrl(self::PAGE_URL);

        $post = $this->request->getParams();

        if (!empty($post)) {
            $name    = $post['full_name'];
            $company = $post['company'] ?: '';
            $phone   = $post['telephone'];
            $email   = $post['email_address'];

            if(!empty($name) && !empty($phone) && !empty($email)) {
                try {
                    $vars = [
                        'fullname' => $name,
                        'company'  => $company,
                        'phone'    => $phone,
                        'email'    => $email
                    ];
                    $transport = $this->helper->getMailDetails(self::EMAIL_TEMPLATE, $vars, '', '', '');
                    $transport->sendMessage();
                    $this->messageManager->addSuccessMessage('Message sent successfully.');
                } catch(MailException $e) {
                    $this->messageManager->addErrorMessage('Issue sending message: ' . $e->getMessage());
                }

            } else {
                $this->messageManager->addErrorMessage('Please fill out all required fields.');
            }
        }

        return $resultRedirect;
    }
}
