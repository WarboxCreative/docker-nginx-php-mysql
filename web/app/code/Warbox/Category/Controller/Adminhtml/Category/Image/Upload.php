<?php declare(strict_types=1);

namespace Warbox\Category\Controller\Adminhtml\Category\Image;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Upload
 * @package Warbox\Category\Controller\Adminhtml\Category\Image
 */
class Upload extends Action
{
    protected ImageUploader $imageUploader;
    protected WriteInterface $mediaDirectory;
    protected StoreManagerInterface $storeManager;
    protected Database $coreFileStorageDatabase;
    protected LoggerInterface $logger;

    /**
     * Upload constructor.
     *
     * @param Context               $context
     * @param ImageUploader         $imageUploader
     * @param Filesystem            $filesystem
     * @param StoreManagerInterface $storeManager
     * @param Database              $coreFileStorageDatabase
     * @param LoggerInterface       $logger
     *
     * @throws FileSystemException
     */
    public function __construct(
        Context               $context,
        ImageUploader         $imageUploader,
        Filesystem            $filesystem,
        StoreManagerInterface $storeManager,
        Database              $coreFileStorageDatabase,
        LoggerInterface       $logger
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->logger = $logger;
    }
    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Warbox_Category::category');
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        try {
            $result = $this->imageUploader->saveFileToTmpDir('landing_image');
            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
