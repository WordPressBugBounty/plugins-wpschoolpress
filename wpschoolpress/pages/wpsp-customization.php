<?php
if ( ! defined( 'ABSPATH' ) ) exit( 'No Such File' );
wpsp_header();
if ( is_user_logged_in() ) {
	global $current_user;
	$current_user_role = $current_user->roles[0];
	if ($current_user_role == 'administrator' || $current_user_role == 'teacher' ) {
            $prefill_name    = $current_user->display_name;
            $prefill_email   = $current_user->user_email;
            $prefill_subject = 'I want quote for customization work';
		wpsp_topbar();
		wpsp_sidebar();
		wpsp_body_start();
		?>
		<div class="wpsp-card">
			<div class="wpsp-card-body">
				<div class="wpsp-row">
					<div class="wpsp-col-md-8 wpsp-col-lg-8">
						<div class="wpsp-form-group">
                            <div id="wpsp-customization-response" class="alert" style="display:none;"></div>
                        </div>
                        <form id="wpspCustomizationForm" method="post">
                            <?php wp_nonce_field( 'wpsp_customization_request', 'wpsp_customization_nonce' ); ?>
                            <div class="wpsp-form-group">
                                <label for="wpsp_custom_name"><?php esc_html_e( 'Your Name', 'wpschoolpress' ); ?> <span class="wpsp-required">*</span></label>
                                <input type="text" id="wpsp_custom_name" name="wpsp_custom_name" class="wpsp-form-control" value="<?php echo esc_attr( $prefill_name ); ?>" placeholder="<?php esc_attr_e( 'Enter your name', 'wpschoolpress' ); ?>" autocomplete="off">
                            </div>
                            <div class="wpsp-form-group">
                                <label for="wpsp_custom_email"><?php esc_html_e( 'Email Address', 'wpschoolpress' ); ?> <span class="wpsp-required">*</span></label>
                                <input type="email" id="wpsp_custom_email" name="wpsp_custom_email" class="wpsp-form-control" value="<?php echo esc_attr( $prefill_email ); ?>" placeholder="<?php esc_attr_e( 'Enter your email', 'wpschoolpress' ); ?>" autocomplete="off">
                            </div>
                            <div class="wpsp-form-group">
                                <label for="wpsp_custom_type"><?php esc_html_e( 'Customization Type', 'wpschoolpress' ); ?> <span class="wpsp-required">*</span></label>
                                <select id="wpsp_custom_type" name="wpsp_custom_type" class="wpsp-form-control">
                                    <option value=""><?php esc_html_e( '-- Select Type --', 'wpschoolpress' ); ?></option>
                                    <option value="feature"><?php esc_html_e( 'New Feature Request', 'wpschoolpress' ); ?></option>
                                    <option value="ui"><?php esc_html_e( 'UI / Design Change', 'wpschoolpress' ); ?></option>
                                    <option value="integration"><?php esc_html_e( 'Third-Party Integration', 'wpschoolpress' ); ?></option>
                                    <option value="report"><?php esc_html_e( 'Custom Report / Export', 'wpschoolpress' ); ?></option>
                                    <option value="other"><?php esc_html_e( 'Other', 'wpschoolpress' ); ?></option>
                                </select>
                            </div>

                            <div class="wpsp-form-group">
                                <label><?php esc_html_e( 'Subject', 'wpschoolpress' ); ?> <span class="wpsp-required">*</span></label>
                                <div class="wpsp-checkbox-group">
                                    <label class="wpsp-checkbox-inline">
                                        <input type="checkbox" id="wpsp_custom_subject_mobile" name="wpsp_custom_subject[]" value="mobile_app">
                                        <?php esc_html_e( 'Mobile App Inquiry', 'wpschoolpress' ); ?>
                                    </label>
                                    <label class="wpsp-checkbox-inline">
                                        <input type="checkbox" id="wpsp_custom_subject_plugin" name="wpsp_custom_subject[]" value="plugin_customization">
                                        <?php esc_html_e( 'Plugin Customization Request', 'wpschoolpress' ); ?>
                                    </label>
                                </div>
                            </div>

                            <div class="wpsp-form-group">
                                <label for="wpsp_custom_description"><?php esc_html_e( 'Description', 'wpschoolpress' ); ?> <span class="wpsp-required">*</span></label>
                                <textarea id="wpsp_custom_description" name="wpsp_custom_description" class="wpsp-form-control" rows="6" placeholder="<?php esc_attr_e( 'Describe your customization requirement in detail...', 'wpschoolpress' ); ?>"></textarea>
                            </div>

							<div class="wpsp-form-group">
								<label for="wpsp_custom_budget"><?php esc_html_e( 'Estimated Budget (Optional)', 'wpschoolpress' ); ?></label>
								<input type="text" id="wpsp_custom_budget" name="wpsp_custom_budget" class="wpsp-form-control" placeholder="<?php esc_attr_e( 'e.g. $100 - $500', 'wpschoolpress' ); ?>" autocomplete="off">
							</div>
							<div class="wpsp-form-group">
								<label for="wpsp_custom_website"><?php esc_html_e( 'Your Website URL', 'wpschoolpress' ); ?></label>
								<input type="url" id="wpsp_custom_website" name="wpsp_custom_website" class="wpsp-form-control" placeholder="<?php esc_attr_e( 'https://yoursite.com', 'wpschoolpress' ); ?>" autocomplete="off">
								<input type="hidden" id="wpsp_custom_siteurl" name="wpsp_custom_siteurl" value="<?php echo esc_attr( site_url() ); ?>">
							</div>
							<div class="wpsp-form-group">
								<button type="submit" id="wpspCustomizationSubmit" class="wpsp-btn wpsp-btn-primary">
									<i class="fa fa-paper-plane"></i>&nbsp; <?php esc_html_e( 'Submit Request', 'wpschoolpress' ); ?>
								</button>
							</div>
						</form>
					</div>
					<div class="wpsp-col-md-4 wpsp-col-lg-4">
						<div class="wpsp-customization-info-box">
							<h4><i class="fa fa-info-circle"></i> <?php esc_html_e( 'How It Works', 'wpschoolpress' ); ?></h4>
							<ul>
								<li><?php esc_html_e( 'Fill in your customization requirement in the form.', 'wpschoolpress' ); ?></li>
								<li><?php esc_html_e( 'Our team will review your request within 1-2 business days.', 'wpschoolpress' ); ?></li>
								<li><?php esc_html_e( 'We\'ll reach out via email with a proposal and timeline.', 'wpschoolpress' ); ?></li>
								<li><?php esc_html_e( 'Once agreed, development begins and you get the update.', 'wpschoolpress' ); ?></li>
							</ul>
							<hr>
							<p><i class="fa fa-globe"></i> <a href="https://wpschoolpress.com" target="_blank" rel="noopener noreferrer">wpschoolpress.com</a></p>
						</div>
					</div>
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