<?php declare(strict_types=1);

namespace Warbox\Blog\Block;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Mageplaza\Blog\Block\Post\Listpost;
use Mageplaza\Blog\Helper\Data as HelperData;
use Mageplaza\Blog\Model\Post;

/**
 * Class Frontend
 *
 * @package Warbox\Blog\Block
 */
class Frontend extends Listpost
{
    /**
     * @param Post $post
     *
     * @return Phrase|string
     * @throws LocalizedException
     */
    public function getPostInfo( $post): Phrase|string
    {
        try {
            $likeCollection = $this->postLikeFactory->create()->getCollection();
            $couldLike      = $likeCollection->addFieldToFilter('post_id', $post->getId())
                ->addFieldToFilter('action', '1')->count();
            $html           = __(
                '<i class="mp-blog-icon mp-blog-calendar-times"></i> %1',
                $this->getDateFormat($post->getPublishDate())
            );

            if ($categoryPost = $this->getPostCategoryHtml($post)) {
                $html .= __('| %1', $categoryPost);
            }

            $author = $this->helperData->getAuthorByPost($post);
            if ($author && $author->getName() && $this->helperData->showAuthorInfo()) {
                $aTag = '<a class="mp-info" href="' . $author->getUrl() . '">'
                    . $this->escapeHtml($author->getName()) . '</a>';
                $html .= __('| <i class="mp-blog-icon mp-blog-user"></i> %1', $aTag);
            }
        } catch (Exception $e) {
            $html = '';
        }

        return $html;
    }
}
