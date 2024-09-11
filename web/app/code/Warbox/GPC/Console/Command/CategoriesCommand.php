<?php declare(strict_types=1);

namespace Warbox\GPC\Console\Command;

use Exception;
use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Filesystem;
use Magento\Framework\File\Csv;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CategoriesCommand
 * @package Warbox\GPC\Console\Command
 */
class CategoriesCommand extends Command
{
    private const ROOT_CATEGORY_ID = 2;

    private const PIM_URL = 'https://pim.gpcind.co.uk';

    private CategoryRepositoryInterface $categoryRepository;

    private CategoryFactory $categoryFactory;

    private CategoryInterfaceFactory $interfaceFactory;

    private Csv $fileCsv;

    private DirectoryList $directoryList;

    private StoreManagerInterface $storeManager;

    private CategoryProductLinkInterfaceFactory $productLinkFactory;

    private CategoryLinkRepositoryInterface $categoryLinkRepository;

    private CollectionFactory $categoryCollection;

    private State $state;

    private Registry $registry;

    private ProductRepositoryInterface $productRepository;

    private Filesystem $filesystem;

    private bool $dryRunMode = false;
    private bool $deleteMode = false;

    private array $requiredHeaders = [
        'id',
        'name',
        'parent_id'
    ];

    private array $optionalHeaders = [
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

    // CSV header row as array
    private array $headersMap;

    private array $baseParentCategories;

    private array $childCategories;

    private array $errors;

    private array $updatedCats = [];

    private array $newCats = [];

    private array $changedCats = [];

    private array $deletedCats = [];

    private string $mediaPath = '';

    /**
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface                $categoryRepository
     * @param \Magento\Catalog\Model\CategoryFactory                          $categoryFactory
     * @param \Magento\Framework\File\Csv                                     $fileCsv
     * @param \Magento\Framework\App\Filesystem\DirectoryList                 $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory   $productLinkFactory
     * @param \Magento\Catalog\Api\CategoryLinkRepositoryInterface            $categoryLinkRepository
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
     * @param \Magento\Catalog\Api\Data\CategoryInterfaceFactory              $interfaceFactory
     * @param \Magento\Framework\App\State                                    $state
     * @param \Magento\Framework\Registry                                     $registry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                 $productRepository
     * @param \Magento\Framework\Filesystem                                   $filesystem
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategoryFactory $categoryFactory,
        Csv $fileCsv,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManager,
        CategoryProductLinkInterfaceFactory $productLinkFactory,
        CategoryLinkRepositoryInterface $categoryLinkRepository,
        CollectionFactory $categoryCollection,
        CategoryInterfaceFactory $interfaceFactory,
        State $state,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        Filesystem $filesystem
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
        $this->fileCsv = $fileCsv;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->productLinkFactory = $productLinkFactory;
        $this->categoryLinkRepository = $categoryLinkRepository;
        $this->categoryCollection = $categoryCollection;
        $this->interfaceFactory = $interfaceFactory;
        $this->state = $state;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->filesystem = $filesystem;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure() : void
    {
        $this->setName('import:categories')
            ->setDescription('Run category importer script')
            ->setDefinition([
                new InputOption(
                    'path',
                    'p',
                    InputOption::VALUE_REQUIRED,
                    'Enter path to the CSV file.'
                ),
                new InputOption(
                    'dry-run',
                    'd',
                    InputOption::VALUE_NONE,
                    'Enable dry-run mode to not update or delete anything.'
                ),
                new InputOption(
                    'remove',
                    'R',
                    InputOption::VALUE_NONE,
                    'Enable delete mode.'
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
            $this->registry->register('isSecureArea', true);
            $this->state->setAreaCode(Area::AREA_ADMINHTML);

            $this->mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            //$this->storeManager->setCurrentStore(0);

            $path = $input->getOption('path');
            if (!$path) {
                throw new LocalizedException(__('Please specify the path to file! (eg. "var/import/categories.csv")'));
            }

            if ($input->getOption('dry-run') == '1') {
                $this->dryRunMode = true;
            }

            if ($input->getOption('remove') == '1') {
                $this->deleteMode = true;
            }

            $data = explode("\n", file_get_contents($path));
            foreach ($data as $key => $row) {
                $data[$key] = explode('|', str_replace('"', "", $row));
            }
            array_pop($data);

            if ($this->dryRunMode) {
                $output->writeln('--- DRY RUN ---');
            }

            if ($this->deleteMode) {
                $output->writeln('--- DELETE CATEGORIES ---');
                $this->removeOldCategories($data, $output);
            }

            $count = 0;
            foreach ($data as $row) {
                if ($count === 0) {
                    $this->mapHeaders($row);
                    foreach ($this->requiredHeaders as $requiredHeader) {
                        if (!array_key_exists($requiredHeader, $this->headersMap)) {
                            throw new LocalizedException(__('Required header "'
                                . $requiredHeader . '" is missing, please fix file'));
                        }
                    }
                    $count++;
                    continue;
                }

                if (empty($row[$this->headersMap['id']])
                    || empty($row[$this->headersMap['name']])
                    || !isset($row[$this->headersMap['parent_id']])) {
                    continue;
                }

                if ($row[$this->headersMap['parent_id']] == 'NULL'
                    || $row[$this->headersMap['parent_id']] == 'null'
                    || $row[$this->headersMap['parent_id']] == ''
                    || !$row[$this->headersMap['parent_id']]) {
                    $this->baseParentCategories[] = $row;
                } else {
                    $this->childCategories[] = $row;
                }
            }

            $output->writeln('--- UPDATE / ADD CATEGORIES ---');
            // Update parent category.
            foreach (!empty($this->baseParentCategories) ? $this->baseParentCategories : [] as $category) {
                $this->addOrUpdateCategory($category, $output, $data);
            }

            // Update child category.
            foreach (!empty($this->childCategories) ? $this->childCategories : [] as $category) {
                $this->addOrUpdateCategory($category, $output, $data);
            }

            // Fail if errors found.
            if (!empty($this->errors)) {
                $output->writeln('There were ' . count($this->errors) . ' error(s):');
                foreach ($this->errors as $error) {
                    $output->writeln($error);
                }
            }

            $output->writeln('Updated Cats: ' . print_r($this->updatedCats, true));
            $output->writeln('New Cats: ' . print_r($this->newCats, true));
            $output->writeln('Deleted Cats: ' . print_r($this->deletedCats, true));
            $output->writeln('Changed Cats: ' . print_r($this->changedCats, true));

            $output->writeln('Import completed successfully!');

            return Cli::RETURN_SUCCESS;
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->writeln($e->getTraceAsString());

            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * Map headers from file to row keys
     *
     * @param array $row
     */
    protected function mapHeaders(array $row) : void
    {
        $headers = array_merge($this->requiredHeaders, $this->optionalHeaders);
        foreach ($row as $key => $item) {
            foreach ($headers as $header) {
                if ($item == $header) {
                    $this->headersMap[$header] = $key;
                }
            }
        }
    }

    /**
     * Add or update category data
     *
     * @param array                                             $data
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array                                             $pimData
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function addOrUpdateCategory(array $data, OutputInterface $output, array $pimData) : bool
    {
        $rootCategoryId = self::ROOT_CATEGORY_ID;
        $category = null;
        $found = false;
        $oldData = [];
        //$this->storeManager->setCurrentStore(0);

        $count = 0;
        $pimCats = [];
        foreach ($pimData as $row) {
            if ($count === 0) {
                $this->mapHeaders($row);
                foreach ($this->requiredHeaders as $requiredHeader) {
                    if (! array_key_exists($requiredHeader, $this->headersMap)) {
                        throw new LocalizedException(__('Required header "'
                            . $requiredHeader . '" is missing, please fix file'));
                    }
                }
                $count++;
                continue;
            }

            $pimCats[] = [
                'id'      => $row[$this->headersMap['id']],
                'name'    => $row[$this->headersMap['name']],
                'url_key' => $this->getAttributeValue($row, 'url_key', ''),
                'parent_id' => $row[$this->headersMap['parent_id']]
            ];
        }

        $catColl = $this->categoryCollection->create();
        $catColl->addAttributeToSelect('*')
            //->setStoreId(0)
            ->addFieldToFilter('name', $data[$this->headersMap['name']]);

        if ($this->dryRunMode) {
            $output->writeln('################');
        }

        foreach ($catColl as $category_item) {
            $categoryFactory = $this->categoryFactory->create();
            $_category = $categoryFactory->load($category_item->getId());
            $found = false;

            foreach ($pimCats as $pim_cat) {
                if ($category === null) {
                    if ($_category && $_category->getName() === $pim_cat[ 'name' ]) {
                        if ($this->dryRunMode) {
                            $output->writeln('[CAT NAME]: ' . $_category->getName());
                            $output->writeln('[CAT ID]: ' . $_category->getId());
                            $output->writeln('[PIM CAT NAME]: ' . $pim_cat[ 'name' ]);
                        }

                        foreach ($pimCats as $pimCat) {
                            if ($pim_cat[ 'parent_id' ] === $pimCat[ 'id' ]) {
                                if ($this->dryRunMode) {
                                    $output->writeln('[PIM PARENT ID]: ' . $pim_cat[ 'parent_id' ]);
                                    $output->writeln('[PIM CAT ID]: ' . $pimCat[ 'id' ]);
                                }

                                if ($_category->getParentCategory() && $_category->getParentCategory()->getName() === $pimCat[ 'name' ]) {
                                    if ($this->dryRunMode) {
                                        $output->writeln('--- LOCAL CAT PARENT NAME == PIM PARENT CAT NAME ---');
                                        $output->writeln('[CAT PARENT NAME]: ' . $_category->getParentCategory()->getName());
                                        $output->writeln('[PIM PARENT NAME]: ' . $pimCat[ 'name' ]);
                                    }
                                    $found = true;
                                    $category = $_category;
                                    $output->writeln('### UPDATE CATEGORY ###');
                                    $output->writeln('Name: ' . $data[ $this->headersMap[ 'name' ] ]);
                                    if (!in_array($data[0], $this->updatedCats, true)) {
                                        $this->updatedCats[] = $data[ 0 ];
                                    }
                                } else {
                                    if ($this->dryRunMode) {
                                        $output->writeln('No Parent category or parent category name does not match: ' . $pimCat['name']);
                                        $output->writeln('pimCat: ' . print_r($pimCat, true));
                                        $output->writeln('_category: ' . $_category->getName());
                                        $output->writeln('pim_cat: ' . print_r($pim_cat, true));
                                    }
                                    $found = false;
                                }
                            }
                        }
                    }
                }
            }

            if (!$found) {
                if (!in_array($data[0], $this->newCats, true) && !in_array($data[0], $this->updatedCats, true)) {
                    $category = $this->categoryFactory->create();
                    $output->writeln('### NEW CATEGORY ###');
                    $output->writeln('Name: ' . $data[ $this->headersMap[ 'name' ] ]);
                    if ( $this->dryRunMode ) {
                        $output->writeln('Data: ' . print_r($data, true));
                    }

                    $this->newCats[] = $data[0];
                }
            }
        }

        if ($category !== null && $found) {
            if (!in_array($data[0], $this->updatedCats, true)) {
                $this->updatedCats[] = $data[ 0 ];
            }
        }

        if ($category === null) {
            if (!in_array($data[0], $this->newCats, true) && !in_array($data[0], $this->updatedCats, true)) {
                $category = $this->categoryFactory->create();
                $output->writeln('### [NOT FOUND] NEW CATEGORY ###');
                $output->writeln('Name: ' . $data[ $this->headersMap[ 'name' ] ]);

                $this->newCats[] = $data[0];
            }
        }

        if ($data[$this->headersMap['name']] === 'Root Catalog' ||
            $data[$this->headersMap['name']] === 'Default Category'
        ) {
            $output->writeln('### SKIPPED ROOT CATEGORY ###');
            return true;
        }

        if ($this->dryRunMode) {
            $output->writeln('PIM Data: ' . print_r($data, true));
            $output->writeln('////////////////');
        }

        if ($category !== null) {
            $oldData = $category->getData();

            //$category->setStoreId(0);
            $category->setName($data[ $this->headersMap[ 'name' ] ]);
            $category->setIsActive($this->getBoolAttributeValue($data, 'is_active', true));
            $category->setIncludeInMenu($this->getBoolAttributeValue($data, 'include_in_menu', false));
            $category->setPosition((int) $this->getAttributeValue($data, 'position', 1));
            $category->setUrlKey($this->getAttributeValue($data, 'url_key', ''));

            $parentData = null;
            $parentMod = null;
            foreach ( $pimData as $row ) {
                // Get Parent category row data
                if ( $row[ $this->headersMap[ 'id' ] ] === $data[ $this->headersMap[ 'parent_id' ] ] ) {
                    $parentData = $row;
                }
            }

            if ( ! is_null($parentData) ) {
                $parentColl = $this->categoryCollection->create();
                $parentColl->addAttributeToSelect('*')
                    ->addFieldToFilter('name', $parentData[ $this->headersMap[ 'name' ] ]);

                foreach ( $parentColl as $parent_item ) {
                    $categoryFactory = $this->categoryFactory->create();
                    $parentMod = $categoryFactory->load($parent_item->getId());
                }
            }

            if ( $rootCategoryId !== $data[ $this->headersMap[ 'parent_id' ] ] ) {
                // If no existing parent category locally.
                if ( ! $parentMod ) {
                    // But parent data exists in CSV, create it.
                    if ( ! is_null($parentData) ) {
                        $output->writeln('Category: ' . $data[ $this->headersMap[ 'name' ] ] . ' does not have existing parent category!');
                        $output->writeln('#########');
                        $output->writeln('Creating parent category: ' . $parentData[ $this->headersMap[ 'name' ] ]);

                        // Create parent category before child category to stop errors.
                        $this->addOrUpdateCategory($parentData, $output, $pimData);
                        $output->writeln('#########');

                        $parentColl = $this->categoryCollection->create();
                        $parentMod = $parentColl->addAttributeToSelect('*')
                            //->setStoreId(0)
                            ->addFieldToFilter('name', $parentData[ $this->headersMap[ 'name' ] ])->getFirstItem();
                        $output->writeln('Finished parent category: ' . $parentMod->getName() . ' | ID: ' . $parentMod->getId());
                        $output->writeln('#########');
                    } else {
                        // No existing parent category or parent data.
                        $this->errors[] = '[ERROR] Category "'
                            . $data[ $this->headersMap[ 'name' ] ]
                            . '" does not have existing parent category!';

                        return false;
                    }
                }
            }

            if ( $parentMod ) {
                $category->setParentId($parentMod->getId());
            }

            // Set Category images.
            if ( $this->getAttributeValue($data, 'image', '') !== '' ) {
                $remoteImg = self::PIM_URL . $this->getAttributeValue($data, 'image', '');
                $image = $this->correctImagePath($this->getAttributeValue($data, 'image', ''));
                $localFile = str_replace('media/media/', 'media/', str_replace('//', '/', $this->mediaPath . $image));
                file_put_contents($localFile, file_get_contents($remoteImg));
                $category->setImage($image, 'image', true, false);
            }
            if ( $this->getAttributeValue($data, 'landing_image', '') !== '' ) {
                $remoteImg = self::PIM_URL . $this->getAttributeValue($data, 'landing_image', '');
                $image = $this->correctImagePath($this->getAttributeValue($data, 'landing_image', ''));
                $localFile = str_replace('media/media/', 'media/', str_replace('//', '/', $this->mediaPath . $image));
                file_put_contents($localFile, file_get_contents($remoteImg));
                $category->setImage($image, 'landing_image', true, false);
            }
            if ( $this->getAttributeValue($data, 'hero_image', '') !== '' ) {
                $remoteImg = self::PIM_URL . $this->getAttributeValue($data, 'hero_image', '');
                $image = $this->correctImagePath($this->getAttributeValue($data, 'hero_image', ''));
                $localFile = str_replace('media/media/', 'media/', str_replace('//', '/', $this->mediaPath . $image));
                file_put_contents($localFile, file_get_contents($remoteImg));
                $category->setImage($image, 'hero_image', true, false);
            }
            if ( $this->getAttributeValue($data, 'list_image', '') !== '' ) {
                $remoteImg = self::PIM_URL . $this->getAttributeValue($data, 'list_image', '');
                $image = $this->correctImagePath($this->getAttributeValue($data, 'list_image', ''));
                $localFile = str_replace('media/media/', 'media/', str_replace('//', '/', $this->mediaPath . $image));
                file_put_contents($localFile, file_get_contents($remoteImg));
                $category->setImage($image, 'list_image', true, false);
            }
            if ( $this->getAttributeValue($data, 'icon_image', '') !== '' ) {
                $remoteImg = self::PIM_URL . $this->getAttributeValue($data, 'icon_image', '');
                $image = $this->correctImagePath($this->getAttributeValue($data, 'icon_image', ''));
                $localFile = str_replace('media/media/', 'media/', str_replace('//', '/', $this->mediaPath . $image));
                file_put_contents($localFile, file_get_contents($remoteImg));
                $category->setImage($image, 'icon_image', true, false);
            }

            $additionalData = [];

            if ( $this->getAttributeValue($data, 'description', '') ) {
                $additionalData[ 'description' ] = $this->getAttributeValue($data, 'description', '');
            }

            if ( $this->getAttributeValue($data, 'meta_description', '') ) {
                $additionalData[ 'meta_description' ] = $this->getAttributeValue($data, 'meta_description', '');
            }

            if ( $this->getAttributeValue($data, 'seo_content', '') ) {
                $additionalData[ 'seo_content' ] = $this->getAttributeValue($data, 'seo_content', '');
            }

            $additionalData[ 'meta_title' ] = $this->getAttributeValue($data, 'meta_title', $category->getName());
            $additionalData[ 'is_anchor' ] = true;

            $category->setCustomAttributes($additionalData);

            if (!$this->dryRunMode) {
                if ($category->getName()) {
                    $category = $this->assignProductPositions($category, $data, $output);
                }
            }

            try {
                $hasChanged = false;
                $newData = $category->getData();

                if ($newData !== $oldData) {
                    $hasChanged = true;
                }

                if ($category->getName() && ($category->hasDataChanges() || $hasChanged)) {
                    if (! $this->dryRunMode) {
                        $this->categoryRepository->save($category);
                    }

                    if (!in_array($data[0], $this->changedCats, true)) {
                        $this->changedCats[] = $data[ 0 ];
                    }
                }
            } catch (CouldNotSaveException $e) {
                $output->writeln('### COULD NOT SAVE CATEGORY ###');
                $output->writeln('Name: ' . $category->getName());
                $output->writeln('URL Key: ' . $category->getUrlKey());
                $output->writeln('Error: ' . $e->getMessage());
                return true;
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $oldPath
     *
     * @return string
     */
    protected function correctImagePath(string $oldPath): string
    {
        $newPath = str_replace(
            '.renditions/wysiwyg/CategoryImages',
            'catalog/category',
            $oldPath
        );

        return str_replace(
            '.renditions/catalog/category',
            'catalog/category',
            $newPath
        );
    }

    /**
     * @param Category                                          $category
     * @param array                                             $data
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return Category
     */
    protected function assignProductPositions(Category $category, array $data, OutputInterface $output) : Category
    {
        if (!$data[$this->headersMap['product_positions']]) {
            return $category;
        }

        $positions = explode(',', $data[$this->headersMap['product_positions']]);

        $catProdsPosition = $category->getProductsPosition();
        foreach ($positions as $positionArr) {
            $prodPosition = explode(';', $positionArr);
            $prodId = $prodPosition[0];
            $position = (int)$prodPosition[1];

            $catProdsPosition[$prodId] = $position;

            try {
                $product = $this->productRepository->get($prodId);

                $categoryProductLink = $this->productLinkFactory->create();
                $categoryProductLink->setCategoryId($category->getEntityId());
                $categoryProductLink->setSku($product->getSku());
                $categoryProductLink->setPosition($position);
                $this->categoryLinkRepository->save($categoryProductLink);
            } catch (NoSuchEntityException|CouldNotSaveException|StateException $e) {
                $output->writeln('### COULD NOT SAVE CATEGORY ###');
                $output->writeln('Name: ' . $category->getName());
                $output->writeln('URL Key: ' . $category->getUrlKey());
                $output->writeln('Error: ' . $e->getMessage());
                return $category;
            }
        }

        return $category;
    }

    /**
     * Returns value of additional attribute required by Magento
     *
     * @param array      $data
     * @param string     $header
     * @param mixed $default
     *
     * @return bool
     */
    protected function getBoolAttributeValue(array $data, string $header, mixed $default) : bool
    {
        if (array_key_exists($header, $this->headersMap) && isset($data[$this->headersMap[$header]])) {
            if ($data[$this->headersMap[$header]] == '1') {
                return true;
            } else {
                return false;
            }
        }

        return $default;
    }

    /**
     * Returns value of additional attribute required by Magento
     *
     * @param array      $data
     * @param string     $header
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getAttributeValue(array $data, string $header, mixed $default) : mixed
    {
        if (array_key_exists($header, $this->headersMap) && isset($data[$this->headersMap[$header]])) {
            return $data[$this->headersMap[$header]];
        }

        return $default;
    }

    /**
     * @param array                                             $pimData
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function removeOldCategories(array $pimData, OutputInterface $output) : void
    {
        $count = 0;
        $pimCats = [];
        foreach ($pimData as $row) {
            if ($count === 0) {
                $this->mapHeaders($row);
                foreach ($this->requiredHeaders as $requiredHeader) {
                    if (! array_key_exists($requiredHeader, $this->headersMap)) {
                        throw new LocalizedException(__('Required header "'
                            . $requiredHeader . '" is missing, please fix file'));
                    }
                }
                $count++;
                continue;
            }

            $pimCats[] = [
                'id'      => $row[$this->headersMap['id']],
                'name'    => $row[$this->headersMap['name']],
                'url_key' => $this->getAttributeValue($row, 'url_key', ''),
                'parent_id' => $row[$this->headersMap['parent_id']]
            ];
        }

        $catColl = $this->categoryCollection->create();
        $catColl->addAttributeToSelect('*');

        foreach ($catColl as $category) {
            $categoryFactory = $this->categoryFactory->create();
            $category = $categoryFactory->load($category->getId());
            $found = false;

            foreach ($pimCats as $pim_cat) {
                if ($category->getName() === $pim_cat['name']) {
                    $found = true;

                    foreach ($pimCats as $pimCat) {
                        if ($pim_cat['parent_id'] === $pimCat['id']) {
                            if ($category->getParentCategory()->getName() !== $pimCat['name']) {
                                $found = false;
                            }
                        }
                    }
                }
            }

            if (!$found && $category->getName() !== 'Non PIM Categories') {
                if ($this->dryRunMode) {
                    $output->writeln('### DELETE CATEGORY ###');
                    $output->writeln('ID: '.$category->getId());
                    $output->writeln('Name: '.$category->getName());
                    $this->deletedCats[] = $category->getId();
                } else {
                    $categoryFactory = $this->categoryFactory->create();
                    $category = $categoryFactory->load($category->getId());

                    if ($category->getId()) {
                        if ($category->getUrlKey() !== 'special-offers' && $category->getUrlKey() !== 'bestsellers') {
                            $output->writeln('DELETE: ' . $category->getId() . ' | ' . $category->getName());
                            $this->categoryRepository->deleteByIdentifier($category->getId());
                            $this->deletedCats[] = $category->getId();
                        }
                    }
                }
            }
        }
    }
}
