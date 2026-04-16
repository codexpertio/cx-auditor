<?php
namespace Codexpert\CX_Auditor\Checkers;

class SSL_Check extends Abstract_Checker {

	public function slug(): string {
		return 'ssl_check';
	}

	public function label(): string {
		return __( 'SSL Certificate', 'cx-auditor' );
	}

	protected function do_checks( string $site_url ): array {
		$checks  = [];
		$url     = rtrim( $site_url, '/' );

		$is_https = str_starts_with( $url, 'https://' );

		$checks[] = $this->check(
			'https_enabled',
			__( 'HTTPS Enabled', 'cx-auditor' ),
			$is_https ? 'pass' : 'fail',
			$is_https ? 'yes' : 'no',
			'yes'
		);

		if ( $is_https ) {
			$hostname = parse_url( $url, PHP_URL_HOST );
			$context  = stream_context_create( [ 'ssl' => [ 'verify_peer' => true, 'timeout' => 10 ] ] );
			$conn     = @fsockopen( $hostname, 443, $errno, $errstr, 10 );

			if ( $conn ) {
				fclose( $conn );
				$checks[] = $this->check( 'ssl_valid', __( 'SSL Valid', 'cx-auditor' ), 'pass', 'yes', 'yes' );
			} else {
				$checks[] = $this->check( 'ssl_valid', __( 'SSL Valid', 'cx-auditor' ), 'warning', 'unknown', 'valid' );
			}
		}

		return $checks;
	}
}