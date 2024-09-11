<?php declare(strict_types=1);

namespace Warbox\GPC\Console\Command;

use Exception;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateCategories
 * @package Warbox\GPC\Console\Command
 */
class GenerateCategories extends Command
{
    private CategoryFactory $categoryFactory;

    protected CategoryRepository $categoryRepository;

    protected CollectionFactory $collectionFactory;

    protected DirectoryList $directoryList;

    protected StoreManagerInterface $storeManager;

    protected ProductRepositoryInterface $productRepository;

    private array $headers = [
        'id',
        'name',
        'parent_id',
        'is_active',
        'include_in_menu',
        'description',
        'seo_content',
        'meta_title',
        'meta_description',
        'url_key',
        'position',
        'product_positions',
        'image',
        'landing_image',
        'hero_image',
        'list_image',
        'icon_image'
    ];

    private array $data;

    private array $errors;

    /**
     * @param \Magento\Catalog\Model\CategoryFactory                          $categoryFactory
     * @param \Magento\Catalog\Model\CategoryRepository                       $categoryRepository
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList                 $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                 $productRepository
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryRepository $categoryRepository,
        CollectionFactory $collectionFactory,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
        $this->directoryList = $directoryList;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->storeManager->setCurrentStore(1);
        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure() : void
    {
        $this->setName('generate:category:export')
            ->setDescription('Generates a CSV of category data')
            ->setDefinition([
                new InputOption(
                    'path',
                    'p',
                    InputOption::VALUE_REQUIRED,
                    'Enter a path to save the CSV file to, relative to the web root. Defaults to pub/feeds/'
                )
            ]);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        try {
            $path = $input->getOption('path');
            if (!$path) {
                $path = 'pub/feeds';
            }

            $file = $this->directoryList->getRoot() . '/' . $path . '/category_export-' . date('Y-m-d') . '.csv';
            $fp = fopen($file, 'w+');

            fputcsv($fp, $this->headers, '|');

            $this->getCategoryData();

            foreach ($this->data as $data) {
                fputcsv($fp, $data, '|');
            }

            fclose($fp);

            // Fail if errors found.
            if (!empty($this->errors)) {
                $output->writeln('There were ' . count($this->errors) . ' error(s):');
                foreach ($this->errors as $error) {
                    $output->writeln($error);
                }
            } else {
                $output->writeln('Export completed successfully!');
            }

            return Cli::RETURN_SUCCESS;
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->writeln($e->getTraceAsString());

            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    private function getStoreId() : int
    {
        $storeId = $this->storeManager->getStore();
        return (int)$storeId->getId();
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCategoryData() : void
    {
        $categories = $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addUrlRewriteToResult()
            ->setStore($this->getStoreId());

        /** @var Category $category */
        foreach ($categories as $category) {
            $catProdsPosition = $category->getProductsPosition();

            $description = $category->getCustomAttribute('description') ?
                $category->getCustomAttribute('description')->getValue() :
                '';
            $seoContent = $category->getCustomAttribute('seo_content') ?
                $category->getCustomAttribute('seo_content')->getValue() :
                '';
            $metaTitle = $category->getCustomAttribute('meta_title') ?
                $category->getCustomAttribute('meta_title')->getValue() :
                '';
            $metaDesc = $category->getCustomAttribute('meta_description') ?
                $category->getCustomAttribute('meta_description')->getValue() :
                '';

            $this->data[] = [
                'id' => $category->getEntityId(),
                'name' => $category->getName(),
                'parent_id' => $category->getParentId(),
                'is_active' => $category->getIsActive(),
                'include_in_menu' => $category->getIncludeInMenu(),
                'description' => $description,
                'seo_content' => $seoContent,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDesc,
                'url_key' => $category->getUrlKey(),
                'position' => $category->getPosition(),
                'product_positions' => $this->getProductPositions($catProdsPosition),
                'image' => $category->getImageUrl('image'),
                'landing_image' => $category->getImageUrl('landing_image'),
                'hero_image' => $category->getImageUrl('hero_image'),
                'list_image' => $category->getImageUrl('list_image'),
                'icon_image'  => $category->getImageUrl('icon_image')
            ];
        }
    }

    /**
     * @param array $prodPositions
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getProductPositions(array $prodPositions) : string
    {
        $prodArr = '';
        foreach ($prodPositions as $prodId => $position) {
            $product = $this->productRepository->getById($prodId);
            $prodArr .= $product->getSku() . ';' . $position . ',';
        }

        return rtrim($prodArr, ',');
    }
}
