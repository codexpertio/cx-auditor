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
			<p>
				<label for="site_url"><?php esc_html_e( 'Client Site URL', 'cx-auditor' ); ?></label><br>
				<input type="url" id="site_url" name="site_url" class="regular-text" placeholder="https://example.com" style="width: 300px; max-width: 100%;">
			</p>
			<p class="submit">
				<button type="button" class="button button-primary button-hero" id="cx-auditor-run">
					<?php esc_html_e( 'Run Quick Audit', 'cx-auditor' ); ?>
				</button>
			</p>
		</form>
		<p id="cx-auditor-status" class="cx-auditor-status"></p>
	</div>

	<div id="cx-auditor-results" style="display:none;">
		<div class="cx-auditor-card">
			<h2><?php esc_html_e( 'Audit Results', 'cx-auditor' ); ?></h2>
			<div id="cx-auditor-score" class="cx-auditor-gauge" data-score="0">0/100</div>
			<p id="cx-auditor-actions" style="margin-top:12px;">
				<a id="cx-auditor-download" class="button button-primary" href="#" target="_blank">
					<?php esc_html_e( 'Download PDF', 'cx-auditor' ); ?>
				</a>
			</p>
			<div id="cx-auditor-modules"></div>
		</div>
	</div>
</div>