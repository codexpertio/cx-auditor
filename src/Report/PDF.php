<?php
namespace Codexpert\CX_Auditor\Report;

use Dompdf\Dompdf;
use Dompdf\Options;

defined( 'ABSPATH' ) || exit;

class PDF {

	public const OPTION_WHITELABEL = 'cx_auditor_whitelabel';

	public function generate( array $data ): string {
		if ( ! class_exists( Dompdf::class ) ) {
			return '';
		}

		$html = $this->render_html( $data );

		$options = new Options();
		$options->set( 'isRemoteEnabled', true );
		$options->set( 'defaultFont', 'sans-serif' );

		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( $html );
		$dompdf->setPaper( 'A4', 'portrait' );
		$dompdf->render();

		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'cx-auditor';
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$filename = 'cx-auditor-report-' . time() . '.pdf';
		$path     = $dir . '/' . $filename;
		file_put_contents( $path, $dompdf->output() );

		return $path;
	}

	private function render_html( array $data ): string {
		$site_url     = $data['site_url'];
		$overall      = $data['overall'];
		$modules      = $data['modules'];
		$generated    = $data['generated'];

		$whitelabel = (array) get_option( self::OPTION_WHITELABEL, [] );
		$brand      = esc_html( $whitelabel['brand'] ?? 'CX Auditor' );
		$color      = esc_attr( $whitelabel['color'] ?? '#7c3aed' );
		$logo       = esc_url( $whitelabel['logo'] ?? '' );

		ob_start();
		?>
		<html>
		<head>
			<meta charset="utf-8">
			<style>
				body { font-family: sans-serif; color: #111; font-size: 12px; }
				.header { border-bottom: 3px solid <?php echo $color; ?>; padding-bottom: 12px; margin-bottom: 20px; }
				.header h1 { margin: 0; color: <?php echo $color; ?>; }
				.score-big { font-size: 48px; font-weight: bold; color: <?php echo $color; ?>; }
				.module { margin-bottom: 16px; page-break-inside: avoid; }
				.module h3 { margin: 0 0 6px; padding: 6px 10px; background: #f3f4f6; }
				.badge { display: inline-block; padding: 2px 8px; font-size: 10px; border-radius: 3px; color: #fff; }
				.badge-pass { background: #16a34a; }
				.badge-warning { background: #ca8a04; }
				.badge-fail { background: #dc2626; }
				table { width: 100%; border-collapse: collapse; }
				td, th { padding: 4px 6px; border-bottom: 1px solid #e5e7eb; font-size: 11px; text-align: left; }
				.meta td { border: 0; padding: 2px 6px; }
				.recs { background: #fef3c7; padding: 10px; margin-top: 8px; }
				.recs ul { margin: 4px 0 0 18px; }
			</style>
		</head>
		<body>
			<div class="header">
				<?php if ( $logo ) : ?>
					<img src="<?php echo $logo; ?>" style="max-height: 40px; float: right;" />
				<?php endif; ?>
				<h1><?php echo $brand; ?> &mdash; <?php esc_html_e( 'Quick Audit Report', 'cx-auditor' ); ?></h1>
				<table class="meta">
					<tr><td><strong><?php esc_html_e( 'Site', 'cx-auditor' ); ?>:</strong></td><td><?php echo esc_html( $site_url ); ?></td></tr>
					<tr><td><strong><?php esc_html_e( 'Generated', 'cx-auditor' ); ?>:</strong></td><td><?php echo esc_html( $generated ); ?></td></tr>
				</table>
			</div>

			<div style="text-align:center; margin-bottom:24px;">
				<div class="score-big"><?php echo (int) $overall; ?>/100</div>
				<div><?php echo esc_html( self::band( (int) $overall ) ); ?></div>
			</div>

			<?php foreach ( $modules as $slug => $mod ) : ?>
				<?php $result = $mod['result']; ?>
				<div class="module">
					<h3>
						<span class="badge badge-<?php echo esc_attr( $result['status'] ); ?>"><?php echo esc_html( strtoupper( $result['status'] ) ); ?></span>
						<?php echo esc_html( $mod['label'] ); ?>
						<span style="float:right;">Score: <?php echo (int) $result['score']; ?>/100</span>
					</h3>
					<table>
						<tr><th>Check</th><th>Status</th><th>Value</th><th>Expected</th></tr>
						<?php foreach ( $result['checks'] as $c ) : ?>
							<tr>
								<td><?php echo esc_html( $c['label'] ); ?></td>
								<td><span class="badge badge-<?php echo esc_attr( $c['status'] ); ?>"><?php echo esc_html( strtoupper( $c['status'] ) ); ?></span></td>
								<td><?php echo esc_html( (string) ( $c['value'] ?? '-' ) ); ?></td>
								<td><?php echo esc_html( (string) ( $c['expected'] ?? '-' ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</table>
					<?php if ( ! empty( $result['recommendations'] ) ) : ?>
						<div class="recs">
							<strong><?php esc_html_e( 'Recommendations', 'cx-auditor' ); ?></strong>
							<ul>
								<?php foreach ( $result['recommendations'] as $r ) : ?>
									<li><?php echo esc_html( $r ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<div style="margin-top:30px; padding:12px; background:#f9fafb; border:1px solid #e5e7eb; font-size:10px;">
				<strong><?php esc_html_e( 'Note', 'cx-auditor' ); ?>:</strong>
				<?php esc_html_e( 'This is a preliminary audit based on publicly accessible information. For a comprehensive analysis including user accounts, plugins, database health, and more, please use the full WP Auditor plugin installed on the target site.', 'cx-auditor' ); ?>
			</div>
		</body>
		</html>
		<?php
		return (string) ob_get_clean();
	}

	public static function band( int $score ): string {
		if ( $score >= 90 ) {
			return __( 'Excellent', 'cx-auditor' );
		}
		if ( $score >= 70 ) {
			return __( 'Good', 'cx-auditor' );
		}
		if ( $score >= 50 ) {
			return __( 'Needs Work', 'cx-auditor' );
		}
		return __( 'Critical', 'cx-auditor' );
	}
}