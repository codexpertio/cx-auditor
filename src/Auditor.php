<?php
namespace Codexpert\CX_Auditor;

use Codexpert\CX_Auditor\Checkers\Checker_Interface;
use Codexpert\CX_Auditor\Checkers\WordPress_Detection;
use Codexpert\CX_Auditor\Checkers\SSL_Check;
use Codexpert\CX_Auditor\Checkers\Basic_Security;
use Codexpert\CX_Auditor\Checkers\Basic_SEO;

defined( 'ABSPATH' ) || exit;

class Auditor {

	private array $checkers = [];

	public function __construct() {
		$this->checkers = [
			new WordPress_Detection(),
			new SSL_Check(),
			new Basic_Security(),
			new Basic_SEO(),
		];
	}

	public function run( string $site_url ): array {
		$results = [];
		$total_score = 0;
		$count = 0;

		foreach ( $this->checkers as $checker ) {
			$result = $checker->run( $site_url );
			$results[ $checker->slug() ] = [
				'label'   => $checker->label(),
				'result'  => $result,
			];
			$total_score += $result['score'];
			$count++;
		}

		$overall_score = $count > 0 ? (int) round( $total_score / $count ) : 0;

		return [
			'site_url'   => $site_url,
			'overall'    => $overall_score,
			'modules'    => $results,
			'generated'  => current_time( 'mysql' ),
		];
	}

	public static function run_ajax(): void {
		check_ajax_referer( 'cx_auditor_audit', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );
		}

		$site_url = isset( $_POST['site_url'] ) ? esc_url_raw( wp_unslash( $_POST['site_url'] ) ) : '';

		if ( empty( $site_url ) ) {
			wp_send_json_error( [ 'message' => 'Site URL is required' ], 400 );
		}

		if ( ! filter_var( $site_url, FILTER_VALIDATE_URL ) ) {
			wp_send_json_error( [ 'message' => 'Invalid URL format' ], 400 );
		}

		$auditor = new self();
		$result  = $auditor->run( $site_url );

		wp_send_json_success( $result );
	}
}