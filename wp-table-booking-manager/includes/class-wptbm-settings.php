<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPTBM_Settings {
	public static function get_defaults() {
		return [
			'admin_email' => get_option( 'admin_email' ),
			'from_email' => '',
			'business_hours' => [
				// 0=Sun .. 6=Sat
				['closed' => true,  'open' => '09:00', 'close' => '17:00'],
				['closed' => false, 'open' => '09:00', 'close' => '22:00'],
				['closed' => false, 'open' => '09:00', 'close' => '22:00'],
				['closed' => false, 'open' => '09:00', 'close' => '22:00'],
				['closed' => false, 'open' => '09:00', 'close' => '22:00'],
				['closed' => false, 'open' => '09:00', 'close' => '23:00'],
				['closed' => false, 'open' => '09:00', 'close' => '23:00'],
			],
			'custom1_label' => __( 'Special request', 'wptbm' ),
			'custom1_required' => false,
			'customer_email_subject' => __( 'Your booking request #{booking_id}', 'wptbm' ),
			'customer_email_template' => __( "Hi {customer_name},\n\nThanks for your request at {site_name}.\n\nDetails:\n- Table: {table_title}\n- Date: {date} {time}\n- Party size: {party_size}\n- Status: {status}\n- Notes: {notes}\n\nWe will confirm your booking shortly.\n\nRegards,\n{site_name}", 'wptbm' ),
			'admin_email_subject' => __( 'New booking request #{booking_id}', 'wptbm' ),
			'admin_email_template' => __( "New booking request:\n- Customer: {customer_name} ({customer_email}, {customer_phone})\n- Table: {table_title}\n- Date: {date} {time}\n- Party size: {party_size}\n- Notes: {notes}\n- Custom: {custom1}\n- Status: {status}", 'wptbm' ),
		];
	}

	public static function get_settings() {
		$defaults = self::get_defaults();
		$opts = get_option( 'wptbm_settings', [] );
		if ( ! is_array( $opts ) ) { $opts = []; }
		return wp_parse_args( $opts, $defaults );
	}

	public static function register_menu() {
		add_submenu_page(
			'edit.php?post_type=wptbm_booking',
			__( 'Booking Settings', 'wptbm' ),
			__( 'Settings', 'wptbm' ),
			'manage_options',
			'wptbm-settings',
			[ __CLASS__, 'render_settings_page' ]
		);
	}

	public static function register_settings() {
		register_setting( 'wptbm_settings', 'wptbm_settings', [ __CLASS__, 'sanitize' ] );
	}

	public static function sanitize( $input ) {
		$defaults = self::get_defaults();
		$output = [];
		$output['admin_email'] = isset( $input['admin_email'] ) ? sanitize_email( $input['admin_email'] ) : $defaults['admin_email'];
		$output['from_email'] = isset( $input['from_email'] ) ? sanitize_email( $input['from_email'] ) : '';
		$output['custom1_label'] = isset( $input['custom1_label'] ) ? sanitize_text_field( $input['custom1_label'] ) : $defaults['custom1_label'];
		$output['custom1_required'] = ! empty( $input['custom1_required'] ) ? true : false;

		$output['business_hours'] = [];
		for ( $d = 0; $d <= 6; $d++ ) {
			$closed = ! empty( $input['business_hours'][ $d ]['closed'] );
			$open = isset( $input['business_hours'][ $d ]['open'] ) ? sanitize_text_field( $input['business_hours'][ $d ]['open'] ) : '09:00';
			$close = isset( $input['business_hours'][ $d ]['close'] ) ? sanitize_text_field( $input['business_hours'][ $d ]['close'] ) : '22:00';
			$output['business_hours'][ $d ] = [ 'closed' => $closed, 'open' => $open, 'close' => $close ];
		}

		$output['customer_email_subject'] = isset( $input['customer_email_subject'] ) ? sanitize_text_field( $input['customer_email_subject'] ) : $defaults['customer_email_subject'];
		$output['customer_email_template'] = isset( $input['customer_email_template'] ) ? wp_kses_post( $input['customer_email_template'] ) : $defaults['customer_email_template'];
		$output['admin_email_subject'] = isset( $input['admin_email_subject'] ) ? sanitize_text_field( $input['admin_email_subject'] ) : $defaults['admin_email_subject'];
		$output['admin_email_template'] = isset( $input['admin_email_template'] ) ? wp_kses_post( $input['admin_email_template'] ) : $defaults['admin_email_template'];
		return $output;
	}

	public static function render_settings_page() {
		$settings = self::get_settings();
		$days = [ __( 'Sunday', 'wptbm' ), __( 'Monday', 'wptbm' ), __( 'Tuesday', 'wptbm' ), __( 'Wednesday', 'wptbm' ), __( 'Thursday', 'wptbm' ), __( 'Friday', 'wptbm' ), __( 'Saturday', 'wptbm' ) ];
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Booking Settings', 'wptbm' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'wptbm_settings' ); ?>
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Admin email', 'wptbm' ); ?></th>
							<td><input type="email" name="wptbm_settings[admin_email]" value="<?php echo esc_attr( $settings['admin_email'] ); ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'From email (optional)', 'wptbm' ); ?></th>
							<td><input type="email" name="wptbm_settings[from_email]" value="<?php echo esc_attr( $settings['from_email'] ); ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Custom field label', 'wptbm' ); ?></th>
							<td>
								<input type="text" name="wptbm_settings[custom1_label]" value="<?php echo esc_attr( $settings['custom1_label'] ); ?>" class="regular-text" />
								<label><input type="checkbox" name="wptbm_settings[custom1_required]" value="1" <?php checked( $settings['custom1_required'] ); ?> /> <?php esc_html_e( 'Required', 'wptbm' ); ?></label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Business Hours', 'wptbm' ); ?></th>
							<td>
								<table>
									<thead>
										<tr><th><?php esc_html_e( 'Day', 'wptbm' ); ?></th><th><?php esc_html_e( 'Closed', 'wptbm' ); ?></th><th><?php esc_html_e( 'Open', 'wptbm' ); ?></th><th><?php esc_html_e( 'Close', 'wptbm' ); ?></th></tr>
									</thead>
									<tbody>
									<?php for ( $d = 0; $d <= 6; $d++ ) : $row = $settings['business_hours'][ $d ]; ?>
										<tr>
											<td><?php echo esc_html( $days[ $d ] ); ?></td>
											<td><input type="checkbox" name="wptbm_settings[business_hours][<?php echo esc_attr( $d ); ?>][closed]" value="1" <?php checked( $row['closed'] ); ?> /></td>
											<td><input type="time" name="wptbm_settings[business_hours][<?php echo esc_attr( $d ); ?>][open]" value="<?php echo esc_attr( $row['open'] ); ?>" /></td>
											<td><input type="time" name="wptbm_settings[business_hours][<?php echo esc_attr( $d ); ?>][close]" value="<?php echo esc_attr( $row['close'] ); ?>" /></td>
										</tr>
									<?php endfor; ?>
									</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Customer email subject', 'wptbm' ); ?></th>
							<td><input type="text" name="wptbm_settings[customer_email_subject]" value="<?php echo esc_attr( $settings['customer_email_subject'] ); ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Customer email template', 'wptbm' ); ?></th>
							<td><textarea name="wptbm_settings[customer_email_template]" class="large-text" rows="8"><?php echo esc_textarea( $settings['customer_email_template'] ); ?></textarea></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Admin email subject', 'wptbm' ); ?></th>
							<td><input type="text" name="wptbm_settings[admin_email_subject]" value="<?php echo esc_attr( $settings['admin_email_subject'] ); ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Admin email template', 'wptbm' ); ?></th>
							<td><textarea name="wptbm_settings[admin_email_template]" class="large-text" rows="8"><?php echo esc_textarea( $settings['admin_email_template'] ); ?></textarea></td>
						</tr>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}