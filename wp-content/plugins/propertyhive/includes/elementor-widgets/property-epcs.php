<?php
/**
 * Elementor Property EPCs Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_EPCs_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-epcs';
	}

	public function get_title() {
		return __( 'EPCs', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-chart-line';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'epc', 'epcs' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'EPCs', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'show_title',
			[
				'label' => __( 'Show Title', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'propertyhive' ),
				'label_off' => __( 'Hide', 'propertyhive' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => __( 'Title Typography', 'propertyhive' ),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .epcs h4',
				'condition' => [
		            'show_title' => 'yes'
		        ],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Title Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .epcs h4' => 'color: {{VALUE}}',
				],
				'condition' => [
		            'show_title' => 'yes'
		        ],
			]
		);

		$this->end_controls_section();

	}

	protected function render() {

		global $property;

		$settings = $this->get_settings_for_display();

		if ( !isset($property->id) ) {
			return;
		}

		if ( isset($settings['show_title']) && $settings['show_title'] != 'yes' )
		{
?>
<style type="text/css">
.epcs h4 { display:none; }
</style>
<?php
		}

        $epc_attachment_ids = $property->get_epc_attachment_ids();

		if ( !empty($epc_attachment_ids) )
		{
			echo '<div class="epcs">';

				echo '<h4>' . __( 'EPCs', 'propertyhive' ) . '</h4>';

				foreach ( $epc_attachment_ids as $attachment_id )
				{
					if ( wp_attachment_is_image($attachment_id) )
                    {
						echo '<a href="' . wp_get_attachment_url($attachment_id) . '" data-fancybox="epc" rel="nofollow"><img src="' . wp_get_attachment_url($attachment_id) . '" alt=""></a>';
					}
					else
					{
						echo '<a href="' . wp_get_attachment_url($attachment_id) . '" target="_blank" rel="nofollow">' . __( 'View EPC', 'propertyhive' ) . '</a>';
					}
				}

			echo '</div>';
		}

	}

}