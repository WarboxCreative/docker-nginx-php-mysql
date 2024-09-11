<?php declare(strict_types=1);

namespace Warbox\GPC\Console\Command;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Console\Cli;
use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Wyomind\MassProductImport\Model\Profiles;
use Wyomind\MassProductImport\Model\ResourceModel\Profiles\Collection;

/**
 * Class AddMapping
 * @package Warbox\GPC\Console\Command
 */
class AddMapping extends Command
{
    private const SYSTEM_ATTRS = [
        'sku',
        'product_type',
        'price',
        'qty',
        'configurable_attributes',
        'children_skus',
        'base_image',
        'small_image',
        'thumbnail_image',
        'categories',
        'attribute_set',
        'website',
        'tax_class',
        'manage_stock',
        'use_config_manage_stock',
        'is_in_stock',
        'status',
        'images',
        'media_gallery',
        'min_sale_qty',
        'max_sale_qty',
        'use_config_min_sale_qty',
        'use_config_max_sale_qty',
        'tier_price',
        'updated_at',
        'created_at',
    ];
    private const ATTR_SOURCE = [
        'sku' => '0',
        'children_skus' => '1',
        'gpc_linked_bundle_product' => '2',
        'product_type' => '3',
        'categories' => '4',
        'name' => '5',
        'description' => '6',
        'short_description' => '7',
        'visibility' => '8',
        'price' => '9',
        'special_price' => '10',
        'tier_price' => '11',
        'base_image' => '12',
        'small_image' => '13',
        'thumbnail_image' => '14',
        'meta_title' => '15',
        'meta_description' => '16',
        'tax_class' => '17',
        'updated_at' => '18',
        'url_key' => '19',
        'gpc_4_wheel_sack_truck_hl_mm' => '20',
        'gpc_base' => '21',
        'gpc_base_size_d_x_w_mm' => '22',
        'gpc_base_size_mm' => '23',
        'gpc_basket_size_mm' => '24',
        'gpc_bay' => '25',
        'gpc_bearing_type' => '26',
        'gpc_belt_colour' => '27',
        'gpc_belt_depth_mm' => '28',
        'gpc_belt_length_mm' => '29',
        'gpc_belt_message' => '30',
        'gpc_bin_type' => '31',
        'gpc_brand' => '32',
        'gpc_capacity' => '33',
        'gpc_capacity_litres' => '34',
        'gpc_capacity_per_lm' => '35',
        'gpc_child_skus' => '36',
        'gpc_clearance_between_trays_mm' => '37',
        'gpc_closed_height_mm' => '38',
        'gpc_closed_length_mm' => '39',
        'gpc_colour' => '40',
        'gpc_colour_option' => '41',
        'gpc_compartments' => '42',
        'gpc_configuration' => '43',
        'gpc_container_size_l_x_w_x_h_mm' => '44',
        'gpc_contents' => '45',
        'gpc_cubic_capacity_litres' => '46',
        'gpc_datasheet' => '47',
        'gpc_depth_mm' => '48',
        'gpc_description' => '49',
        'gpc_desktop_worktop_colour' => '50',
        'gpc_desk_colour' => '51',
        'gpc_diameter' => '52',
        'gpc_distance_between_uprights_mm' => '53',
        'gpc_distributor' => '54',
        'gpc_doors' => '55',
        'gpc_door_colour' => '56',
        'gpc_drawers' => '57',
        'gpc_drawer_size' => '58',
        'gpc_extended_length_mm' => '59',
        'gpc_external_size_d_x_h_mm' => '60',
        'gpc_external_size_l_x_w_x_h_mm' => '61',
        'gpc_external_size_mm' => '62',
        'gpc_external__size_mm' => '63',
        'gpc_features' => '64',
        'gpc_finish' => '65',
        'gpc_fire_rating' => '66',
        'gpc_fitting' => '67',
        'gpc_fixing' => '68',
        'gpc_folded_height_mm' => '69',
        'gpc_folded_size_h_x_w_x_d' => '70',
        'gpc_folded_size_h_x_w_x_d_mm' => '71',
        'gpc_folded_size_l_x_w_x_h_mm' => '72',
        'gpc_fork_length_mm' => '73',
        'gpc_for_use_with' => '74',
        'gpc_frame_colour' => '75',
        'gpc_frame_w_x_d_x_h_mm' => '76',
        'gpc_handle_height_mm' => '77',
        'gpc_height_mm' => '78',
        'gpc_housing_colours' => '79',
        'gpc_housing_size_h_x_w_x_d_mm' => '80',
        'gpc_hub_bore_mm' => '81',
        'gpc_image_disclaimer' => '82',
        'gpc_includes' => '83',
        'gpc_internal_height_mm' => '84',
        'gpc_internal_size_d_x_h_mm' => '85',
        'gpc_internal_size_h_x_w_x_d_mm' => '86',
        'gpc_internal_size_l_x_w_mm' => '87',
        'gpc_internal_size_l_x_w_x_h_mm' => '88',
        'gpc_internal_size_w_x_d_x_h_mm' => '89',
        'gpc_internal_size__mm' => '90',
        'gpc_is_workbench' => '91',
        'gpc_lead_time' => '92',
        'gpc_length' => '93',
        'gpc_length_extended' => '94',
        'gpc_length_mm' => '95',
        'gpc_lid_colour' => '96',
        'gpc_lift' => '97',
        'gpc_lift_height_mm' => '98',
        'gpc_lift_speed_cm_sec' => '99',
        'gpc_list_attribute' => '100',
        'gpc_loaded_height_mm' => '101',
        'gpc_load_capacity_kg' => '102',
        'gpc_lowered_fork_height_mm' => '103',
        'gpc_lowered_platform_height_mm' => '104',
        'gpc_material' => '105',
        'gpc_maximum_extended_width_mm' => '106',
        'gpc_maximum_lift_height_mm' => '107',
        'gpc_mesh_size_mm' => '108',
        'gpc_model' => '109',
        'gpc_model_type' => '110',
        'gpc_no_of_baskets' => '111',
        'gpc_no_of_bays' => '112',
        'gpc_no_of_bins' => '113',
        'gpc_no_of_boxes' => '114',
        'gpc_no_of_compartments' => '115',
        'gpc_no_of_containers' => '116',
        'gpc_no_of_decking_tiles' => '117',
        'gpc_no_of_doors' => '118',
        'gpc_no_of_drawers' => '119',
        'gpc_no_of_drums_stored' => '120',
        'gpc_no_of_hooks' => '121',
        'gpc_no_of_legs' => '122',
        'gpc_no_of_pockets' => '123',
        'gpc_no_of_rungs' => '124',
        'gpc_no_of_seats' => '125',
        'gpc_no_of_sections' => '126',
        'gpc_no_of_shelves' => '127',
        'gpc_no_of_sloping_shelves' => '128',
        'gpc_no_of_steps' => '129',
        'gpc_no_of_tiers' => '130',
        'gpc_no_of_trays' => '131',
        'gpc_no_of_treads' => '132',
        'gpc_number_of_cylinders_held' => '133',
        'gpc_open_length_mm' => '134',
        'gpc_open_size_w_x_d_x_h_mm' => '135',
        'gpc_optional_extras' => '136',
        'gpc_outer_size_w_x_d_x_h_mm' => '137',
        'gpc_overall_height_mm' => '138',
        'gpc_overall_size_folded_h_x_w_x_d_mm' => '139',
        'gpc_overall_size_h_x_d_x_w_mm' => '140',
        'gpc_overall_size_h_x_w_mm' => '141',
        'gpc_overall_size_h_x_w_x_d_mm' => '142',
        'gpc_overall_size_h__x_w_mm' => '143',
        'gpc_overall_size_l_x_w_mm' => '144',
        'gpc_overall_size_l_x_w_x_h_mm' => '145',
        'gpc_overall_size_open_h_x_w_x_d_mm' => '146',
        'gpc_overall_size_w_x_d_mm' => '147',
        'gpc_overall_size_w_x_l_x_h_mm' => '148',
        'gpc_packaging' => '149',
        'gpc_pack_contents' => '150',
        'gpc_pack_qty' => '151',
        'gpc_pack_size' => '152',
        'gpc_pallet_type' => '153',
        'gpc_paper_size' => '154',
        'gpc_pim_product' => '155',
        'gpc_platform_height' => '156',
        'gpc_platform_height_mm' => '157',
        'gpc_platform_size_l_x_w_mm' => '158',
        'gpc_platform_size_l_x_w_x_h_mm' => '159',
        'gpc_platform_size_mm' => '160',
        'gpc_platform_size_w_x_d_mm' => '161',
        'gpc_platform_truck_mode_l_x_w_x_h_mm' => '162',
        'gpc_platform_type' => '163',
        'gpc_pockets_per_column' => '164',
        'gpc_post_colour' => '165',
        'gpc_post_colours' => '166',
        'gpc_product_badges' => '167',
        'gpc_product_video' => '168',
        'gpc_quantity' => '169',
        'gpc_raised_fork_height_mm' => '170',
        'gpc_raised_platform_height_mm' => '171',
        'gpc_rollers' => '172',
        'gpc_roller_diameter_mm' => '173',
        'gpc_roller_pitch' => '174',
        'gpc_sack_colour' => '175',
        'gpc_sack_truck_mode_l_x_w_x_h_mm' => '176',
        'gpc_seat_colour' => '177',
        'gpc_shackle_dia_mm' => '178',
        'gpc_shelf_heights_mm' => '179',
        'gpc_shelf_material' => '180',
        'gpc_shelf_size_l_x_w_mm' => '181',
        'gpc_shelf_size_mm' => '182',
        'gpc_size' => '183',
        'gpc_size_as_step_unit_wdh_mm' => '184',
        'gpc_size_basic_sack_truck_h_mm' => '185',
        'gpc_size_between_shelves_mm' => '186',
        'gpc_size_dia_mm' => '187',
        'gpc_size_d_x_h_mm' => '188',
        'gpc_size_d_x_w_x_h_mm' => '189',
        'gpc_size_h_x_dia_mm' => '190',
        'gpc_size_h_x_l_mm' => '191',
        'gpc_size_h_x_w_mm' => '192',
        'gpc_size_h_x_w_x_d_mm' => '193',
        'gpc_size_l_x_d_mm' => '194',
        'gpc_size_l_x_d_x_h_mm' => '195',
        'gpc_size_l_x_h_mm' => '196',
        'gpc_size_l_x_w_mm' => '197',
        'gpc_size_l_x_w_x_h_mm' => '198',
        'gpc_size_mm' => '199',
        'gpc_size_sack_truck_wdh_mm' => '200',
        'gpc_size_when_folded_w_x_l_mm' => '201',
        'gpc_size_when_open_h_x_w_x_d_mm' => '202',
        'gpc_size_w_x_d_mm' => '203',
        'gpc_size_w_x_d_x_h_mm' => '204',
        'gpc_size_w_x_h_mm' => '205',
        'gpc_size__l_x_w_mm' => '206',
        'gpc_stores' => '207',
        'gpc_suitable_for' => '208',
        'gpc_sump_capacity_l' => '209',
        'gpc_table_size_l_x_w_mm' => '210',
        'gpc_tiers' => '211',
        'gpc_timber_slat_option' => '212',
        'gpc_toe_plate' => '213',
        'gpc_toe_plate_size_w_x_d_mm' => '214',
        'gpc_to_fit' => '215',
        'gpc_to_fit_w_x_d' => '216',
        'gpc_to_suit' => '217',
        'gpc_to_suit_bench_mm' => '218',
        'gpc_to_suit_reel_size' => '219',
        'gpc_to_suit_tubing' => '220',
        'gpc_tray_colour' => '221',
        'gpc_tray_height_mm' => '222',
        'gpc_tread_size_w_x_d_mm' => '223',
        'gpc_tread_type' => '224',
        'gpc_tube_size_internal_mm' => '225',
        'gpc_turnatable_dia_mm' => '226',
        'gpc_type' => '227',
        'gpc_unit_type' => '228',
        'gpc_unloaded_height_mm' => '229',
        'gpc_upright_frame_colour' => '230',
        'gpc_version' => '231',
        'gpc_volume_litres' => '232',
        'gpc_weight_kg' => '233',
        'gpc_wheels' => '234',
        'gpc_wheels_mm' => '235',
        'gpc_wheels_mm_roller_bearing' => '236',
        'gpc_wheel_size_mm' => '237',
        'gpc_wheel_type' => '238',
        'gpc_width_mm' => '239',
        'gpc_width_over_forks_mm' => '240',
        'gpc_workbench_category' => '241',
        'gpc_workbench_options' => '242',
        'gpc_working_height_mm' => '243',
        'gpc_worktop_colour_options' => '244',
        'gpc_worktop_size_w_x_d_mm' => '245',
        'gpc_worktop_type' => '246',
        'configurable_attributes' => '247',
        'qty' => '248',
        'min_sale_qty' => '249',
        'use_config_min_sale_qty' => '250',
        'max_sale_qty' => '251',
        'use_config_max_sale_qty' => '252',
        'created_at' => '253',
        'images' => '254'
    ];

    protected DirectoryList $directoryList;
    protected StoreManager $storeManager;
    protected Collection $profilesCollection;
    protected Profiles $profilesModel;

    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param Collection       $profilesCollection
     * @param Profiles         $profilesModel
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        Collection       $profilesCollection,
        Profiles         $profilesModel
    ) {
        parent::__construct();
        $this->attributeRepository = $attributeRepository;
        $this->profilesCollection = $profilesCollection;
        $this->profilesModel = $profilesModel;
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    public function configure(): void
    {
        $this->setName('warbox:gpc:addmapping')
            ->setDescription('Used to generate a Wyomind import profile')
            ->setDefinition([
                new InputArgument(
                    'profile_id',
                    InputArgument::REQUIRED,
                    'The Mass Product Import & Update profile in which to add the mapping'
                )]);
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): ?int
    {

        $profileId = $input->getArgument('profile_id');
        $headers   = self::ATTR_SOURCE;

        $outputArray = [];

        foreach ($this->setSystemMappings() as $system_mapping) {
            $outputArray[] = $system_mapping;
        }

        foreach ($headers as $header => $index) {
            try {
                if (!in_array($header, self::SYSTEM_ATTRS)) {
                    $attribute = $this->attributeRepository->get(4, $header);

                    $tmp = [
                        'id' => 'Attribute/' . $attribute->getBackendType() . '/' . $attribute->getAttributeId(),
                        'label' => $attribute->getDefaultFrontendLabel(),
                        'index' => $index,
                        'color' => 'rgba(255, 0, 238, 0.5)',
                        'tag' => '',
                        'source' => $header,
                        'default' => '',
                        'scripting' => '',
                        'rule' => '',
                        'configurable' => '0',
                        'importupdate' => '2',
                        'storeviews' => ['0'],
                        'enabled' => true
                    ];
                    $outputArray[] = $tmp;

                    $output->writeln('<info>' . $attribute->getAttributeCode() . ' added!</info>');
                }
            } catch (Exception $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        }

        foreach ($this->setConfigAttributes() as $config_mapping) {
            $outputArray[] = $config_mapping;
        }

        foreach ($this->setChildSkus() as $config_mapping) {
            $outputArray[] = $config_mapping;
        }

        $profile = $this->profilesModel->load($profileId);
        $profile->setId(null);

        $filePath = 'https://pim.gpcind.co.uk/feeds/ssg.csv';
        //$filePath = 'pub/feeds/main.csv';
        $delimiter = ';';
        $enclosure = 'none';
        $name = 'NEW PIM sync ' . date('Y-m-d H:i:d');

        $profile->setName($name);

        $jsonOutput = json_encode($outputArray, JSON_UNESCAPED_SLASHES);
        $profile->setMapping($jsonOutput);
        $profile->setData('sql', 0);
        $profile->setData('identifier_offset', 0);
        $profile->setData('identifier', 'sku');
        $profile->setData('auto_set_instock', 1);
        $profile->setData('profile_method', 3);
        $profile->setData('use_sftp', 0);
        $profile->setData('ftp_active', 0);
        $profile->setData('file_type', 1);
        $profile->setData('file_system_type', 3);
        //$profile->setData('file_system_type', 1);
        $profile->setData('file_path', $filePath);
        $profile->setData('field_delimiter', $delimiter);
        $profile->setData('field_enclosure', $enclosure);
        $profile->setData('use_custom_rules', 0);
        $profile->setData('images_system_type', 2);
        $profile->setData('images_use_sftp', 0);
        $profile->setData('images_ftp_active', 1);
        $profile->setData('product_removal', 1);
        $profile->setData('create_configurable_onthefly', 0);
        $profile->setData('create_category_onthefly', 1);
        $profile->setData('category_is_active', 1);
        $profile->setData('category_include_in_menu', 1);
        $profile->setData('category_parent_id', 1);
        $profile->setData('has_header', 1);
        $profile->setData('tree_detection', 1);
        $profile->setData('post_process_action', 0);
        $profile->setData('post_process_indexers', 1);
        $profile->setData('is_magento_export', 2);
        $profile->setData('relative_stock_update', 0);
        $profile->setData('enabled', 1);
        $profile->setData('images_replace_if_changed', 1);
        $profile->setData('product_target', 2);

        $profile->save();

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @return array
     */
    private function setSystemMappings() : array
    {
        $tmpArr = [];

        $tmpArr[] = [
            'id' => 'Image/media_gallery/161',
            'label' => 'Media Gallery',
            'index' => '254',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => 'Image Attributes',
            'source' => 'images',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'System/type_id',
            'label' => 'Type',
            'index' => '3',
            'color' => 'rgba(0,0,0,0)',
            'tag' => '',
            'source' => 'product_type',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Price/decimal/148',
            'label' => 'Price',
            'index' => '9',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'price',
            'default' => '0',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Stock/qty',
            'label' => 'Qty',
            'index' => '248',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'qty',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Stock/min_sale_qty',
            'label' => 'Min Sale Qty',
            'index' => '249',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'min_sale_qty',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Stock/use_config_min_sale_qty',
            'label' => 'Use Config Min Sale Qty',
            'index' => '250',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'use_config_min_sale_qty',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Stock/max_sale_qty',
            'label' => 'Max Sale Qty',
            'index' => '251',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'max_sale_qty',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Stock/use_config_max_sale_qty',
            'label' => 'Use Config Max Sale Qty',
            'index' => '252',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'use_config_max_sale_qty',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'TierPrice/decimal/163/replace',
            'label' => 'Replace Tier Prices / Group Prices',
            'index' => '11',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'tier_price',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Image/varchar/158',
            'label' => 'Base',
            'index' => '12',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'base_image',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Image/varchar/159',
            'label' => 'Small',
            'index' => '13',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'small_image',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Image/varchar/160',
            'label' => 'Thumbnail',
            'index' => '14',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'thumbnail_image',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Category/mapping',
            'label' => 'Replace all categories with',
            'index' => '4',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'categories',
            'default' => '',
            'scripting' => '<?php __LINE_BREAK__ $str_arr = explode(",", $self);__LINE_BREAK__ __LINE_BREAK__ $flattened = $str_arr;__LINE_BREAK__ array_walk($flattened, function(&$value) {__LINE_BREAK__    $value = "Default Category/{$value}";__LINE_BREAK__});__LINE_BREAK__ __LINE_BREAK__ return implode(",",$flattened);__LINE_BREAK__',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'System/attribute_set_id',
            'label' => 'Attribute set',
            'index' => '',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => '',
            'default' => 'default',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'System/website',
            'label' => 'Website',
            'index' => '',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => '',
            'default' => 'main website',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Attribute/int/205/tax_class_id',
            'label' => 'Tax Class',
            'index' => '',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => '',
            'default' => 'Taxable Goods',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Stock/manage_stock',
            'label' => 'Manage Stock',
            'index' => '',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => '',
            'default' => 'Enabled',
            'scripting' => '<?php __LINE_BREAK__ __LINE_BREAK__ __LINE_BREAK__ switch($cell["product_type"]){__LINE_BREAK__ case "configurable":__LINE_BREAK__ return "No";__LINE_BREAK__ break;__LINE_BREAK__ default:__LINE_BREAK__ return "Yes";__LINE_BREAK__}',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Stock/use_config_manage_stock',
            'label' => 'Use Config Manage Stock',
            'index' => '',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => '',
            'default' => 'Enabled',
            'scripting' => '<?php __LINE_BREAK__ __LINE_BREAK__ __LINE_BREAK__ switch($cell["product_type"]){__LINE_BREAK__ case "configurable":__LINE_BREAK__ return "No";__LINE_BREAK__ break;__LINE_BREAK__ default:__LINE_BREAK__ return "Yes";__LINE_BREAK__}',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Stock/is_in_stock',
            'label' => 'Is In Stock',
            'index' => '',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => '',
            'default' => 'Enabled',
            'scripting' => '<?php __LINE_BREAK__ __LINE_BREAK__ __LINE_BREAK__ switch($cell["product_type"]){__LINE_BREAK__ case "configurable":__LINE_BREAK__ return "No";__LINE_BREAK__ break;__LINE_BREAK__ default:__LINE_BREAK__ return "Yes";__LINE_BREAK__}',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Msi/quantity/default',
            'label' => 'Default Source [default] | Quantity',
            'index' => '248',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => 'qty',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Msi/status/default',
            'label' => 'Default Source [default] | Stock Status',
            'index' => '',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => '',
            'default' => 'Enabled',
            'scripting' => '<?php __LINE_BREAK__ __LINE_BREAK__ __LINE_BREAK__ switch($cell["product_type"]){__LINE_BREAK__ case "configurable":__LINE_BREAK__ return "No";__LINE_BREAK__ break;__LINE_BREAK__ default:__LINE_BREAK__ return "Yes";__LINE_BREAK__}',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Attribute/int/168/status',
            'label' => 'Status',
            'index' => '',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => '',
            'default' => 'Enabled',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'Attribute/decimal/153',
            'label' => 'Weight',
            'index' => '',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => '',
            'default' => '1',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        $tmpArr[] = [
            'id' => 'System/has_options',
            'label' => 'Has Options',
            'index' => '',
            'color' => 'rgba(0, 0, 0, 0)',
            'tag' => '',
            'source' => '',
            'default' => 'Enabled',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        return $tmpArr;
    }

    /**
     * @return array
     */
    private function setConfigAttributes() : array
    {
        $tmpArr = [];

        $tmpArr[] = [
            'id' => 'ConfigurableProduct/attributes',
            'label' => 'Configurable Attributes',
            'index' => '247',
            'color' => 'rgba(255, 143, 143, 0.5)',
            'tag' => 'Configurable Attributes',
            'source' => 'configurable_attributes',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        return $tmpArr;
    }

    /**
     * @return array
     */
    private function setChildSkus() : array
    {
        $tmpArr = [];

        $tmpArr[] = [
            'id' => 'ConfigurableProduct/childrenSkus',
            'label' => 'Children SKUs',
            'index' => '1',
            'color' => 'rgba(255, 143, 143, 0.5)',
            'tag' => '',
            'source' => 'children_skus',
            'default' => '',
            'scripting' => '',
            'rule' => '',
            'configurable' => '0',
            'importupdate' => '2',
            'storeviews' => [
                '0'
            ],
            'enabled' => true
        ];

        return $tmpArr;
    }
}
