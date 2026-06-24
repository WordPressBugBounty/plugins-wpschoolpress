<?php
if ( ! defined( 'ABSPATH' ) ) exit( 'No Such File' );
wpsp_header();
if ( is_user_logged_in() ) {
	global $current_user;
	$current_user_role = $current_user->roles[0];
	if ( $current_user_role === 'administrator' ) {
		wpsp_topbar();
		wpsp_sidebar();
		wpsp_body_start();

		// Fetch addons from EDD REST API (public endpoint, no auth needed)
		$api_response = wp_remote_get( 'https://wpschoolpress.com/edd-api/products/?number=50', array(
			'timeout'   => 10,
			'sslverify' => true,
		) );

		$addons       = array();
		$api_error    = false;

		if ( is_wp_error( $api_response ) ) {
			$api_error = true;
		} else {
			$body = wp_remote_retrieve_body( $api_response );
			$data = json_decode( $body, true );

			if ( ! empty( $data['products'] ) && is_array( $data['products'] ) ) {
				foreach ( $data['products'] as $product ) {
					$info    = isset( $product['info'] )    ? $product['info']    : array();
					$pricing = isset( $product['pricing'] ) ? $product['pricing'] : array();

					// Only show products from the "Addons" category
					$categories = ( isset( $info['category'] ) && is_array( $info['category'] ) ) ? $info['category'] : array();
					$is_addon_category = false;
					foreach ( $categories as $cat ) {
						if ( isset( $cat['slug'] ) && $cat['slug'] === 'addons' ) {
							$is_addon_category = true;
							break;
						}
						if ( isset( $cat['name'] ) && strtolower( $cat['name'] ) === 'addons' ) {
							$is_addon_category = true;
							break;
						}
					}
					if ( ! $is_addon_category ) continue;

					$price_val = '';
					if ( ! empty( $pricing['amount'] ) ) {
						$price_val = is_array( $pricing['amount'] )
							? '$' . reset( $pricing['amount'] )
							: '$' . $pricing['amount'];
					}

					$addons[] = array(
						'name'        => isset( $info['title'] )     ? $info['title']     : '',
						'description' => isset( $info['excerpt'] )   ? $info['excerpt']   : '',
						'url'         => isset( $info['link'] )      ? $info['link']      : '',
						'icon'        => isset( $info['thumbnail'] ) ? $info['thumbnail'] : '',
						'price'       => $price_val,
						'key'         => 'edd_' . ( isset( $info['id'] ) ? intval( $info['id'] ) : 0 ),
						'badge'       => '',
					);
				}
			} else {
				$api_error = true;
			}
		}

		// Installed addons detection — check known addon constants / classes
		$installed_addons = array();
		if ( defined( 'WPSP_ADDON_VERSION' ) || class_exists( 'Wpsp_Addon' ) ) {
			$installed_addons[] = 'wpsp_addon_version';
		}
		if ( defined( 'WPSP_MESSAGE_VERSION' ) || class_exists( 'Wpsp_Message' ) ) {
			$installed_addons[] = 'wpsp_message_version';
		}
		if ( wpsp_check_pro_version( 'wpsp_sms_version' ) && wpsp_check_pro_version( 'wpsp_sms_version' )['status'] ) {
			$installed_addons[] = 'wpsp_sms_version';
		}
		if ( wpsp_check_pro_version( 'pay_WooCommerce' ) && wpsp_check_pro_version( 'pay_WooCommerce' )['status'] ) {
			$installed_addons[] = 'pay_WooCommerce';
		}
		if ( wpsp_check_pro_version( 'wpsp_mc_version' ) && wpsp_check_pro_version( 'wpsp_mc_version' )['status'] ) {
			$installed_addons[] = 'wpsp_mc_version';
		}
		if ( wpsp_check_pro_version( 'wpsp_sm_version' ) && wpsp_check_pro_version( 'wpsp_sm_version' )['status'] ) {
			$installed_addons[] = 'wpsp_sm_version';
		}
		if ( wpsp_check_pro_version( 'wpsp_addon_lms' ) && wpsp_check_pro_version( 'wpsp_addon_lms' )['status'] ) {
			$installed_addons[] = 'wpsp_addon_lms';
		}
		?>
		<div class="wpsp-card">
			<div class="wpsp-card-body">
				<?php if ( $api_error ) : ?>
					<div class="alert alert-warning">
						<i class="fa fa-exclamation-triangle"></i>
						<?php esc_html_e( 'Unable to fetch addons at the moment. Please check your internet connection or try again later.', 'wpschoolpress' ); ?>
					</div>
				<?php elseif ( ! empty( $addons ) ) : ?>
					<div class="wpsp-addons-grid">
						<?php foreach ( $addons as $addon ) :
							$addon_name    = isset( $addon['name'] )        ? sanitize_text_field( $addon['name'] )        : '';
							$addon_desc    = isset( $addon['description'] ) ? wp_kses_post( $addon['description'] )         : '';
							$addon_url     = isset( $addon['url'] )         ? esc_url( $addon['url'] )                      : 'https://wpschoolpress.com/addons/';
							$addon_price   = isset( $addon['price'] )       ? sanitize_text_field( $addon['price'] )        : '';
							$addon_key     = isset( $addon['key'] )         ? sanitize_key( $addon['key'] )                 : '';
							$addon_icon    = isset( $addon['icon'] )        ? esc_url( $addon['icon'] )                     : '';
							$addon_badge   = isset( $addon['badge'] )       ? sanitize_text_field( $addon['badge'] )        : '';
							$is_installed  = in_array( $addon_key, $installed_addons, true );
						?>
						<div class="wpsp-addon-card">
							<div class="wpsp-addon-card-header">
								<?php if ( $addon_icon ) : ?>
									<img src="<?php echo esc_url( $addon_icon ); ?>" alt="<?php echo esc_attr( $addon_name ); ?>" class="wpsp-addon-icon">
								<?php else : ?>
									<div class="wpsp-addon-icon-placeholder">
										<i class="fa fa-puzzle-piece"></i>
									</div>
								<?php endif; ?>
								<?php if ( $addon_badge ) : ?>
									<span class="wpsp-addon-badge"><?php echo esc_html( $addon_badge ); ?></span>
								<?php endif; ?>
								<?php if ( $is_installed ) : ?>
									<span class="wpsp-addon-installed-badge"><i class="fa fa-check-circle"></i> <?php esc_html_e( 'Installed', 'wpschoolpress' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="wpsp-addon-card-body">
								<h4 class="wpsp-addon-name"><?php echo esc_html( $addon_name ); ?></h4>
								<?php if ( $addon_desc ) : ?>
									<p class="wpsp-addon-desc"><?php echo wp_kses_post( $addon_desc ); ?></p>
								<?php endif; ?>
							</div>
							<div class="wpsp-addon-card-footer">
								<?php if ( $addon_price ) : ?>
									<span class="wpsp-addon-price"><?php echo esc_html( $addon_price ); ?></span>
								<?php endif; ?>
								<a href="<?php echo esc_url( $addon_url ); ?>" class="wpsp-btn wpsp-addon-btn" target="_blank" rel="noopener noreferrer">
									<?php if ( $is_installed ) : ?>
										<i class="fa fa-external-link"></i> <?php esc_html_e( 'View Details', 'wpschoolpress' ); ?>
									<?php else : ?>
										<i class="fa fa-shopping-cart"></i> <?php esc_html_e( 'Get Addon', 'wpschoolpress' ); ?>
									<?php endif; ?>
								</a>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<?php
					// Fallback: show static hardcoded addons if API returns no structured data
					$static_addons = array(
						array(
							'name'  => 'WPSchoolPress SMS Addon',
							'desc'  => 'Send SMS notifications to students, parents, and teachers directly from the school dashboard.',
							'url'   => 'https://wpschoolpress.com/addons/',
							'icon'  => 'fa-mobile',
							'key'   => 'wpsp_sms_version',
						),
						array(
							'name'  => 'Online Payment (WooCommerce)',
							'desc'  => 'Accept school fees online via WooCommerce. Supports multiple payment gateways.',
							'url'   => 'https://wpschoolpress.com/addons/',
							'icon'  => 'fa-credit-card',
							'key'   => 'pay_WooCommerce',
						),
						array(
							'name'  => 'Multi-Class Addon',
							'desc'  => 'Allow students to be enrolled in multiple classes simultaneously.',
							'url'   => 'https://wpschoolpress.com/addons/',
							'icon'  => 'fa-graduation-cap',
							'key'   => 'wpsp_mc_version',
						),
						array(
							'name'  => 'Social Media Addon',
							'desc'  => 'Enable a social posts feed inside the school portal for students, teachers, and parents.',
							'url'   => 'https://wpschoolpress.com/addons/',
							'icon'  => 'fa-share-alt',
							'key'   => 'wpsp_sm_version',
						),
						array(
							'name'  => 'LMS Addon',
							'desc'  => 'Add learning management features — lessons, quizzes, and questions — to WPSchoolPress.',
							'url'   => 'https://wpschoolpress.com/addons/',
							'icon'  => 'fa-book',
							'key'   => 'wpsp_addon_lms',
						),
						array(
							'name'  => 'WhatsApp / Messaging Addon',
							'desc'  => 'Send instant WhatsApp and messaging notifications to parents and students.',
							'url'   => 'https://wpschoolpress.com/addons/',
							'icon'  => 'fa-comments',
							'key'   => 'wpsp_message_version',
						),
					);
					?>
					<div class="wpsp-addons-grid">
						<?php foreach ( $static_addons as $addon ) :
							$is_installed = in_array( $addon['key'], $installed_addons, true );
						?>
						<div class="wpsp-addon-card">
							<div class="wpsp-addon-card-header">
								<div class="wpsp-addon-icon-placeholder">
									<i class="fa <?php echo esc_attr( $addon['icon'] ); ?>"></i>
								</div>
								<?php if ( $is_installed ) : ?>
									<span class="wpsp-addon-installed-badge"><i class="fa fa-check-circle"></i> <?php esc_html_e( 'Installed', 'wpschoolpress' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="wpsp-addon-card-body">
								<h4 class="wpsp-addon-name"><?php echo esc_html( $addon['name'] ); ?></h4>
								<p class="wpsp-addon-desc"><?php echo esc_html( $addon['desc'] ); ?></p>
							</div>
							<div class="wpsp-addon-card-footer">
								<a href="<?php echo esc_url( $addon['url'] ); ?>" class="wpsp-btn wpsp-addon-btn" target="_blank" rel="noopener noreferrer">
									<?php if ( $is_installed ) : ?>
										<i class="fa fa-external-link"></i> <?php esc_html_e( 'View Details', 'wpschoolpress' ); ?>
									<?php else : ?>
										<i class="fa fa-shopping-cart"></i> <?php esc_html_e( 'Get Addon', 'wpschoolpress' ); ?>
									<?php endif; ?>
								</a>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="wpsp-addons-footer-note">
					<p>
						<i class="fa fa-info-circle"></i>
						<?php esc_html_e( 'Browse all addons and documentation at', 'wpschoolpress' ); ?>
						<a href="https://wpschoolpress.com/addons/" target="_blank" rel="noopener noreferrer">wpschoolpress.com/addons/</a>
					</p>
				</div>
			</div>
		</div>
		<?php
		wpsp_body_end();
		wpsp_footer();
	} else {
		echo '<p>' . esc_html__( WPSP_PERMISSION_MSG, 'wpschoolpress' ) . '</p>';
	}
}