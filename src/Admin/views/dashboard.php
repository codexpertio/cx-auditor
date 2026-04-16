<?php
/**
 * CX Auditor Dashboard
 *
 * @package Codexpert\CX_Auditor
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap cx-auditor">
	<h1><?php esc_html_e( 'CX Auditor', 'cx-auditor' ); ?></h1>
	<p class="description"><?php esc_html_e( 'Generate a preliminary audit report for any WordPress site using just its URL. No login required.', 'cx-auditor' ); ?></p>

	<div class="cx-auditor-card">
		<h2><?php esc_html_e( 'Audit a Client Site', 'cx-auditor' ); ?></h2>
		<form id="cx-auditor-form">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="site_url"><?php esc_html_e( 'Client Site URL', 'cx-auditor' ); ?></label>
					</th>
					<td>
						<input type="url" id="site_url" name="site_url" class="regular-text" placeholder="https://example.com" required>
						<p class="description"><?php esc_html_e( 'Enter the full URL including https://', 'cx-auditor' ); ?></p>
					</td>
				</tr>
			</table>
			<?php wp_nonce_field( 'cx_auditor_audit', 'cx_auditor_nonce' ); ?>
			<button type="submit" class="button button-primary button-hero" id="cx-auditor-run">
				<?php esc_html_e( 'Run Quick Audit', 'cx-auditor' ); ?>
			</button>
		</form>
		<p id="cx-auditor-status" class="cx-auditor-status"></p>
	</div>

	<div id="cx-auditor-results" style="display:none;">
		<div class="cx-auditor-card">
			<h2><?php esc_html_e( 'Audit Results', 'cx-auditor' ); ?></h2>
			<div id="cx-auditor-score" class="cx-auditor-gauge" data-score="0">0/100</div>
			<div id="cx-auditor-modules"></div>
		</div>
	</div>
</div>