<?php
declare(strict_types=1);

namespace Warbox\Attributes\Plugin\Adminhtml\Product;

use Magento\Catalog\Model\Product;

/**
 * Class Edit
 * @package Warbox\Attributes\Plugin\Adminhtml\Product
 */
class Edit
{
    private const GPC_ATTRS = [
        'gpc_4_wheel_sack_truck_hl_mm',
        'gpc_ac_load',
        'gpc_accessory',
        'gpc_angle_type',
        'gpc_aperture_colour',
        'gpc_arm_reach_mm',
        'gpc_size_basic_sack_truck_h_mm',
        'gpc_size_sack_truck_wdh_mm',
        'gpc_size_as_step_unit_wdh_mm',
        'gpc_audio_output_external',
        'gpc_audio_output_internal',
        'gpc_base',
        'gpc_base_diameter_mm',
        'gpc_base_size_mm',
        'gpc_base_to_platform_mm',
        'gpc_base_unit',
        'gpc_base_shelf_size_mm',
        'gpc_basket_size_mm',
        'gpc_basket_size_wdh_mm',
        'gpc_battery_life',
        'gpc_battery_v_ah',
        'gpc_bay_type',
        'gpc_bay',
        'gpc_length_mm',
        'gpc_beam_levels',
        'gpc_bearing_type',
        'gpc_belt_colour',
        'gpc_bin_1',
        'gpc_bin_2',
        'gpc_bin_3',
        'gpc_bin_4',
        'gpc_bin_5',
        'gpc_bin_quantity',
        'gpc_board_size_wdh_mm',
        'gpc_body_lw_mm',
        'gpc_bolt_centres',
        'gpc_bolt_hole_spacing',
        'gpc_bottom_shelf_size_mm',
        'gpc_box_qty',
        'gpc_boxes',
        'gpc_cabinet_size',
        'gpc_capacity',
        'gpc_capacity_resolution',
        'gpc_capacity_dry',
        'gpc_capacity_litres',
        'gpc_capacity_per_lm',
        'gpc_capacity_wet',
        'gpc_chain',
        'gpc_channel_size_h_x_w_mm',
        'gpc_channel_size_w_x_h_mm',
        'gpc_channel_spacing',
        'gpc_clear_entry_h_x_w_x_d_mm',
        'gpc_clearance',
        'gpc_clearance_between_trays_mm',
        'gpc_closed_height_mm',
        'gpc_colour',
        'gpc_column_sizes_between_mm',
        'gpc_compartments',
        'gpc_complete_with',
        'gpc_configuration',
        'gpc_container_colour',
        'gpc_container_size_l_x_w_x_h_mm',
        'gpc_contents',
        'gpc_cubic_capacity_litres',
        'gpc_cylinder_dia_mm',
        'gpc_deck',
        'gpc_depth_nom',
        'gpc_depth_mm',
        'gpc_description',
        'gpc_details',
        'gpc_diameter',
        'gpc_digits_per_card',
        'gpc_divider',
        'gpc_doors',
        'gpc_drawer_size',
        'gpc_each_compartment_size_mm_w_x_d_x_h',
        'gpc_extended_height_mm',
        'gpc_external__size_mm',
        'gpc_external_height_mm',
        'gpc_external_width_mm',
        'gpc_external_length_mm',
        'gpc_external_depth_mm',
        'gpc_external_diameter_mm',
        'gpc_external_base_size_l_x_w_mm',
        'gpc_external_size_l_x_w_x_h_mm',
        'gpc_external_size_mm',
        'gpc_external_size_w_x_d_x_h_mm',
        'gpc_features',
        'gpc_file_type',
        'gpc_finish',
        'gpc_fire_rating',
        'gpc_fitting',
        'gpc_fixing',
        'gpc_fixing_centres',
        'gpc_fixing_holes',
        'gpc_fixing_required',
        'gpc_folded_size_l_x_w_x_h_mm',
        'gpc_fork_size_l_x_w_mm',
        'gpc_fork_spacing_mm',
        'gpc_fork_spread_mm',
        'gpc_frame_w_x_d_x_h_mm',
        'gpc_frequency_range_mhz',
        'gpc_full_protection',
        'gpc_handle_height_mm',
        'gpc_handle_heights_mm',
        'gpc_height_handle_upright_mm',
        'gpc_height_differential_max_poss',
        'gpc_height_differential_max_rec',
        'gpc_height_extended_to',
        'gpc_height_full',
        'gpc_height_in_position',
        'gpc_height_mm',
        'gpc_height_of_top_tread_mm',
        'gpc_height_under_side_of_drum_mm',
        'gpc_holds',
        'gpc_horizontal_shackle_clearance_mm',
        'gpc_hose_dia_x_l_mm',
        'gpc_hub_bore_mm',
        'gpc_interior_exterior_mirror',
        'gpc_internal_height_mm',
        'gpc_internal_length_mm',
        'gpc_internal_width_mm',
        'gpc_internal_depth_mm',
        'gpc_internal_diameter_mm',
        'gpc_internal_size__mm',
        'gpc_internal_size_l_x_w_mm',
        'gpc_internal_size_l_x_w_x_h_mm',
        'gpc_internal_size_w_x_d_x_h_mm',
        'gpc_item',
        'gpc_jaw_opening',
        'gpc_jaw_opening_thickness',
        'gpc_jaw_opening_width',
        'gpc_key',
        'gpc_kit_type',
        'gpc_label_size_w_x_h_mm',
        'gpc_length',
        'gpc_length_closed',
        'gpc_length_extended',
        'gpc_levels',
        'gpc_lid_colour',
        'gpc_lift_height_mm',
        'gpc_lift_height_per_stroke_mm',
        'gpc_lift_speed_cm_sec',
        'gpc_liners_per_box',
        'gpc_load_capacity_kg',
        'gpc_load_centre_mm',
        'gpc_loaded_height_mm',
        'gpc_locker_size_w_x_d_x_h_mm',
        'gpc_locker_type',
        'gpc_lowered_fork_height_mm',
        'gpc_material',
        'gpc_max_load_height_mm',
        'gpc_max_number_of_containers_held',
        'gpc_max_pressure',
        'gpc_max_watts',
        'gpc_maximum_lift_height_mm',
        'gpc_mesh_size_mm',
        'gpc_model_type',
        'gpc_motor',
        'gpc_no_of_bays',
        'gpc_no_of_bins',
        'gpc_no_of_boxes',
        'gpc_no_of_compartments',
        'gpc_no_of_doors',
        'gpc_no_of_drawers',
        'gpc_no_of_drums_stored',
        'gpc_no_of_files_stored',
        'gpc_no_of_hooks',
        'gpc_no_of_legs',
        'gpc_no_of_levels',
        'gpc_no_of_lockers',
        'gpc_no_of_pans_high',
        'gpc_no_of_rungs',
        'gpc_no_of_seats',
        'gpc_no_of_sections',
        'gpc_no_of_shelves',
        'gpc_no_of_sloping_shelves',
        'gpc_no_of_steps',
        'gpc_no_of_tiers',
        'gpc_no_of_trays',
        'gpc_no_of_treads',
        'gpc_no_of_baskets',
        'gpc_number_of_ends_sides',
        'gpc_open_height_mm',
        'gpc_open_length_mm',
        'gpc_open_width_mm',
        'gpc_open_height_mm',
        'gpc_open_size_l_x_w_x_h_mm',
        'gpc_open_size_w_x_d_x_h_mm',
        'gpc_option',
        'gpc_optional_extras',
        'gpc_outer_size_w_x_d_x_h_mm',
        'gpc_overall_height_mm',
        'gpc_overall_size_h_x_d_x_w_mm',
        'gpc_overall_size_l_x_w_mm',
        'gpc_overall_size_w_x_d_mm',
        'gpc_overall_size_mm',
        'gpc_overlap',
        'gpc_pack',
        'gpc_pack_contents',
        'gpc_pack_qty',
        'gpc_pack_size',
        'gpc_pallet_unit',
        'gpc_pallet_carton_unit',
        'gpc_part_protection',
        'gpc_pick_opening_w_x_h_mm',
        'gpc_pitch',
        'gpc_plate_size_mm',
        'gpc_platform_height_mm',
        'gpc_platform_length_mm',
        'gpc_platform_width_mm',
        'gpc_platform_size_l_x_w_mm',
        'gpc_platform_size_l_x_w_x_h_mm',
        'gpc_platform_size_mm',
        'gpc_platform_size_w_x_d_mm',
        'gpc_platform_trolley_mode_l_x_w_x_h_mm',
        'gpc_size_as_platform_truck_h_x_l_mm',
        'gpc_platform_type',
        'gpc_position_of_door',
        'gpc_pure_kraft_paper',
        'gpc_quantity',
        'gpc_quantity_per_pack',
        'gpc_rail_height_mm',
        'gpc_raised_fork_height_mm',
        'gpc_range',
        'gpc_roller_diameter_mm',
        'gpc_roller_pitch',
        'gpc_run_time',
        'gpc_sack_truck_mode_l_x_w_x_h_mm',
        'gpc_seat_height_mm',
        'gpc_seat_width_mm',
        'gpc_seat_depth_mm',
        'gpc_seat_size_w_x_d_x_h_mm',
        'gpc_series',
        'gpc_shackle_dia_mm',
        'gpc_shelf_configuration',
        'gpc_shelf_heights_mm',
        'gpc_shelf_levels',
        'gpc_shelf_load_kg_udl',
        'gpc_shelf_material',
        'gpc_shelf_size_mm',
        'gpc_side_height_mm',
        'gpc_size__l_x_w_mm',
        'gpc_size_between_shelves_mm',
        'gpc_size_d_x_h_mm',
        'gpc_size_dia_mm',
        'gpc_size_dia_x_h_mm',
        'gpc_size_folded_l_x_w_x_h_mm',
        'gpc_size_when_folded_h_x_w_x_d_mm',
        'gpc_size_h_x_d_mm',
        'gpc_size_h_x_dia_mm',
        'gpc_size_h_x_l_mm',
        'gpc_size_h_x_w_mm',
        'gpc_size_h_x_w_x_d_mm',
        'gpc_size_l_x_d_mm',
        'gpc_size_l_x_d_x_h_mm',
        'gpc_size_l_x_dia_mm',
        'gpc_size_l_x_h_mm',
        'gpc_size_l_x_h_x_dia_mm',
        'gpc_size_l_x_w_mm',
        'gpc_size_l_x_w_x_h_metres',
        'gpc_size_l_x_w_x_h_mm',
        'gpc_size_l_x_w_x_h_mm_per_wall',
        'gpc_size_mm',
        'gpc_size_open_l_x_w_x_h_mm',
        'gpc_size_open_w_x_d_x_h_mm',
        'gpc_size_w_x_d_mm',
        'gpc_size_w_x_d_x_h_mm',
        'gpc_size_w_x_h_mm',
        'gpc_stacked',
        'gpc_stacking_load_kg',
        'gpc_standard_lift_mm',
        'gpc_start_up',
        'gpc_stepladder_height_mm',
        'gpc_stepladder_overall_height_mm',
        'gpc_stores',
        'gpc_style',
        'gpc_suit_bolts_diameter_mm',
        'gpc_suitable_for',
        'gpc_suitable_for_pan',
        'gpc_to_suit_bolt',
        'gpc_sump_capacity_l',
        'gpc_swivel_radius',
        'gpc_table_size_l_x_w_mm',
        'gpc_tag_size_w_x_h_mm',
        'gpc_thickness_mm',
        'gpc_tiers',
        'gpc_to_fit',
        'gpc_to_fit_w_x_d',
        'gpc_to_suit',
        'gpc_to_suit_documents',
        'gpc_to_suit_reel_size',
        'gpc_to_suit_tubing',
        'gpc_toe_plate_size_w_x_d_mm',
        'gpc_top_diameter_mm',
        'gpc_tray_height_mm',
        'gpc_tray_material',
        'gpc_tray_size_mm',
        'gpc_tread_finish',
        'gpc_tread_size_w_x_d_mm',
        'gpc_tread_type',
        'gpc_tread_width_mm',
        'gpc_tube_size_internal_mm',
        'gpc_turntable_dia_mm',
        'gpc_type',
        'gpc_type_of_door',
        'gpc_tyre_width_mm',
        'gpc_unit_type',
        'gpc_unloaded_height_mm',
        'gpc_useable_body_size_w_x_d_x_h_mm',
        'gpc_volume',
        'gpc_volume_litres',
        'gpc_watts',
        'gpc_watts_lpg',
        'gpc_watts_petrol',
        'gpc_weight_kg',
        'gpc_wheel_diameter',
        'gpc_wheel_size_mm',
        'gpc_wheel_type',
        'gpc_wheels',
        'gpc_wheels_mm',
        'gpc_wheels_mm_roller_bearing',
        'gpc_width_mm',
        'gpc_width_over_forks_mm',
        'gpc_worktop_size_w_x_d_mm',
        'gpc_overall_size_w_x_l',
        'gpc_description_pockets',
        'gpc_post_size_h_x_w_mm',
        'gpc_overall_size_l_x_w_x_h_mm',
        'gpc_folded_toe_plate_size_w_x_d_mm',
        'gpc_lift',
        'gpc_overall_size_w_x_l_x_h_mm',
        'gpc_raised_platform_height_mm',
        'gpc_lowered_platform_height_mm',
        'gpc_drawers',
        'gpc_working_height_mm',
        'gpc_capacity_holding_ibcs',
        'gpc_powered',
        'gpc_number_of_cylinders_held',
        'gpc_toe_plate_lifting_height_mm',
        'gpc_toe_plate_size_l_x_d_mm',
        'gpc_cylinder_diameter_mm',
        'gpc_cylinder_capacity_litres',
        'gpc_retaining_strap_heights_mm',
        'gpc_maximum_cylinder_size_mm',
        'gpc_lifting_strap_capacity_kg',
        'gpc_minimum_break_strength_kg',
        'gpc_rated_assembly_strength_kg',
        'gpc_brush_diameter_mm',
        'gpc_brush_speed_rpm',
        'gpc_floor_signal',
        'gpc_bin_type',
        'gpc_fixed_toe_plate_size_w_x_d_mm',
        'gpc_seat_size_h_x_w_mm',
        'gpc_size',
        'gpc_height_when_folded_mm',
        'gpc_size_when_folded_w_x_l_mm',
        'gpc_load_area_w_x_d_x_h_mm',
        'gpc_base_size_l_x_w_x_h_mm',
        'gpc_distance_between_uprights_mm',
        'gpc_zzzzz_quarto_lockers_300d_colour',
        'gpc_zzzzz_quarto_lockers_450d_colour',
        'gpc_size_when_open_h_x_w_x_d_mm',
        'gpc_internal_depth_mm',
        'gpc_no_of_containers',
        'gpc_shelf_load_kg',
        'gpc_truck_description',
        'gpc_model',
        'gpc_bin_size_h_x_w_x_d_mm',
        'gpc_handle_top_colour',
        'gpc_colour_option',
        'gpc_no_of_treads_platform_height',
        'gpc_radio_button_attribute',
        'gpc_pedal_strokes_to_elevate',
        'gpc_platform_truck_mode_l_x_w_x_h_mm',
        'gpc_internal_size_h_x_w_x_d_mm',
        'gpc_seat_colour',
        'gpc_frame_colour',
        'gpc_post_bags_held',
        'gpc_leg_sets',
        'gpc_door_colour',
        'gpc_size_d_x_w_x_h_mm',
        'gpc_post_size',
        'gpc_mounting_plate_size_mm',
        'gpc_folded_height_mm',
        'gpc_tube_load_kg',
        'gpc_includes',
        'gpc_frame_height_mm',
        'gpc_toe_plate_size',
        'gpc_side',
        'gpc_cupboard',
        'gpc_wll_kg',
        'gpc_to_suit_i_beam_mm',
        'gpc_chain_colour',
        'gpc_to_suit_beam_mm',
        'gpc_height_of_lift_m',
        'gpc_buckle_type',
        'gpc_integrated_visor_type',
        'gpc_tray_colour',
        'gpc_plug',
        'gpc_body_colour',
        'gpc_charge_time',
        'gpc_top_shelf_size_w_x_d_mm',
        'gpc_base_size_w_x_d_mm',
        'gpc_post_height_front_mm',
        'gpc_post_height_rear_mm',
        'gpc_bench_load_kg',
        'gpc_worktop_type',
        'gpc_upright_frame_colour',
        'gpc_lid_type',
        'gpc_paper_size',
        'gpc_fabric_colour',
        'gpc_capacity_205_litre_drums',
        'gpc_capacity_1000_litre_ibcs',
        'gpc_max_speed',
        'gpc_pedestal_type',
        'gpc_pallets_stored',
        'gpc_for_use_with',
        'gpc_back_size_h_x_w_mm',
        'gpc_seat_height_minmax_mm',
        'gpc_seat_size_d_x_w_mm',
        'gpc_maximum_extended_width_mm',
        'gpc_no_of_pockets',
        'gpc_size_of_insert',
        'gpc_board_size_w_x_h_mm',
        'gpc_size_a_mm',
        'gpc_size_b_mm',
        'gpc_size_c_mm',
        'gpc_size_d_mm',
        'gpc_ladder_height_mm',
        'gpc_fire_resistance',
        'gpc_volts',
        'gpc_max_load_kg_single_double',
        'gpc_max_load_kg',
        'gpc_remote',
        'gpc_cable',
        'gpc_platform_description',
        'gpc_height_to_top_tread_mm',
        'gpc_flatback_height',
        'gpc_timber_slat_option',
        'gpc_worktop_size_h_x_w_x_d_mm',
        'gpc_visibility_distance_m',
        'gpc_pockets_per_column',
        'gpc_no_of_columns',
        'gpc_length_m',
        'gpc_pallet_type',
        'gpc_no_of_ibcs_stored',
        'gpc_door_aperture_h_x_w_mm',
        'gpc_stroke_mm',
        'gpc_no_of_decking_tiles',
        'gpc_no_of_a4_pages_stored',
        'gpc_board_height_mm',
        'gpc_left_right_board_size_h_x_w',
        'gpc_shelf_size_l_x_w_mm',
        'gpc_no_of_baskets',
        'gpc_no_of_levers',
        'gpc_desktop_worktop_colour',
        'gpc_leg_size',
        'gpc_width_m',
        'gpc_sack_colour',
        'gpc_end_panel',
        'gpc_packaging',
        'gpc_base_colour',
        'gpc_picking_tray_capacity_kg',
        'gpc_base_size_l_x_w_mm',
        'gpc_worktop_colour_options',
        'gpc_overall_size_h__x_w_mm',
        'gpc_a4_pages_stored',
        'gpc_overall_size_dia_x_h_mm',
        'gpc_turnatable_dia_mm',
        'gpc_inner_dia_mm',
        'gpc_installed_size_d_x_w_x_h_mm',
        'gpc_flow_rate_litres_mm',
        'gpc_bsp_inlet_size_inch',
        'gpc_working_pressure_bar_min_max',
        'gpc_overall_size_folded_w_x_d_mm',
        'gpc_folded_size_h_x_w_x_d',
        'gpc_to_suit_locker_size_w_x_d_mm',
        'gpc_overall_size_open_h_x_w_x_d_mm',
        'gpc_overall_size_folded_h_x_w_x_d_mm',
        'gpc_heavy_duty_distribution_trucks_1490_high',
        'gpc_large_heavy_duty_distribution_trucks_1790_high',
        'gpc_post_colours',
        'gpc_belt_style',
        'gpc_optional_shelves',
        'gpc_belt_mount_colours',
        'gpc_carcass_size_h_x_w_mm',
        'gpc_overall_size_h_x_w_x_d_mm',
        'gpc_base_type',
        'gpc_belt_quantity',
        'gpc_housing_colours',
        'gpc_housing_size_h_x_w_x_d_mm',
        'gpc_width_mm_x_length_m',
        'gpc_distributor',
        'gpc_sack_truck_type',
        'gpc_side_end_material',
        'gpc_toe_plate',
        'gpc_load_capacity',
        'gpc_pallet_truck_type',
        'gpc_fork_length',
        'gpc_no_of_shelves_trays',
        'gpc_width_over_forks',
        'gpc_shelf_tray_colour',
        'gpc_lead_time',
        'gpc_guarantee',
        'gpc_rollers',
        'gpc_additional_features',
        'gpc_accreditation',
        'gpc_platform_height',
        'gpc_storage',
        'gpc_lifting_height',
        'gpc_working_load_limit',
        'gpc_drums_stored',
        'gpc_no_of_arms',
        'gpc_body_material',
        'gpc_bench_depth_mm',
        'gpc_linked_bundle_product',
        'gpc_fork_length_mm',
        'gpc_workbench_options',
        'gpc_workbench_category',
        'gpc_is_workbench',
        'gpc_product_badges',
        'gpc_list_attribute',
    ];

    /**
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit $subject
     * @param Product                                       $result
     *
     * @return Product
     */
    public function afterGetProduct( \Magento\Catalog\Block\Adminhtml\Product\Edit $subject, Product $result): Product
    {
        $product = $result;
        foreach(self::GPC_ATTRS as $attr) {
            $product->lockAttribute($attr);
        }

        return $result;
    }
}