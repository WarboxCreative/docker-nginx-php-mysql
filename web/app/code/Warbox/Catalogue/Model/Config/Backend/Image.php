<?php
declare(strict_types=1);

namespace Warbox\Catalogue\Model\Config\Backend;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Image
 *
 * @package Warbox\Catalogue\Model\Config\Backend
 */
class Image extends \Magento\Config\Model\Config\Backend\Image
{
    public const UPLOAD_DIR = 'catalogue';

    /**
     * Return path to directory for upload file
     *
     * @return string
     * @throw \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploadDir(): string
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(self::UPLOAD_DIR));
    }

    /**
     * Makes a decision about whether to add info about the scope.
     *
     * @return bool
     */
    protected function _addWhetherScopeInfo(): bool
    {
        return true;
    }

    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    protected function _getAllowedExtensions(): array
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'svg'];
    }

    /**
     * @return mixed|null
     */
    protected function getTmpFileName(): mixed
    {
        if (isset($_FILES['groups']))
        {
            $tmpName = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
        } else {
            $tmpName = is_array($this->getValue()) ? $this->getValue()['tmp_name'] : null;
        }

        return $tmpName;
    }

    /**
     * Save uploaded file before saving config value
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave(): Image
    {
        $value = $this->getValue();
        $deleteFlag = is_array($value) && !empty($value['delete']);
        if ($this->isTmpFileAvailable($value) && $imageName = $this->getUploadedImageName($value))
        {
            $fileTmpName = $this->getTmpFileName();
            if ($this->getOldValue() && ($fileTmpName || $deleteFlag))
            {
                $this->_mediaDirectory->delete(self::UPLOAD_DIR . '/' . $this->getOldValue());
            }
        }
        return parent::beforeSave();
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function isTmpFileAvailable( $value): bool
    {
        return is_array($value) && isset($value[0]['tmp_name']);
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function getUploadedImageName( $value): string
    {
        if (is_array($value) && isset($value[0]['name']))
        {
            return $value[0]['name'];
        }
        return '';
    }
}
