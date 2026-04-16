<?php
namespace Codexpert\CX_Auditor\Checkers;

defined( 'ABSPATH' ) || exit;

abstract class Abstract_Checker implements Checker_Interface {

	protected array $recommendations = [];

	abstract protected function do_checks( string $site_url ): array;

	public function run( string $site_url ): array {
		$this->recommendations = [];
		$checks                = $this->do_checks( $site_url );

		$total = count( $checks );
		$fails = 0;
		$warns = 0;
		foreach ( $checks as $c ) {
			$status = $c['status'] ?? 'pass';
			if ( 'fail' === $status ) {
				$fails++;
			} elseif ( 'warning' === $status ) {
				$warns++;
			}
		}

		$score = 100;
		if ( $total > 0 ) {
			$deduction = ( ( $fails * 100 ) + ( $warns * 50 ) ) / $total;
			$score     = (int) max( 0, min( 100, round( 100 - $deduction ) ) );
		}

		$status = 'pass';
		if ( $fails > 0 || $score < 50 ) {
			$status = 'fail';
		} elseif ( $warns > 0 || $score < 90 ) {
			$status = 'warning';
		}

		return [
			'status'          => $status,
			'score'           => $score,
			'checks'          => $checks,
			'recommendations' => array_values( array_unique( $this->recommendations ) ),
		];
	}

	protected function check( string $key, string $label, string $status, $value = null, $expected = null, ?array $details = null ): array {
		return [
			'check_key' => $key,
			'label'     => $label,
			'status'    => $status,
			'value'     => $this->scalarize( $value ),
			'expected'  => $this->scalarize( $expected ),
			'details'   => $details,
		];
	}

	protected function recommend( string $text ): void {
		$text = trim( $text );
		if ( '' !== $text ) {
			$this->recommendations[] = $text;
		}
	}

	protected function http_get( string $url, array $args = [] ): ?array {
		$response = wp_remote_get( $url, array_merge( [ 'timeout' => 15, 'redirection' => 5 ], $args ) );
		if ( is_wp_error( $response ) ) {
			return null;
		}
		return [
			'code'    => (int) wp_remote_retrieve_response_code( $response ),
			'body'    => (string) wp_remote_retrieve_body( $response ),
			'headers' => wp_remote_retrieve_headers( $response ),
		];
	}

	private function scalarize( $val ): ?string {
		if ( null === $val ) {
			return null;
		}
		if ( is_bool( $val ) ) {
			return $val ? 'true' : 'false';
		}
		if ( is_scalar( $val ) ) {
			return (string) $val;
		}
		return wp_json_encode( $val );
	}
}