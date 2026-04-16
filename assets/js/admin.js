/* global jQuery, CXAuditor */
(function ($) {
	'use strict';

	var currentData = null;

	function renderGauge($el, score) {
		var color = '#dc2626';
		if (score >= 90) color = '#16a34a';
		else if (score >= 70) color = '#65a30d';
		else if (score >= 50) color = '#ca8a04';
		var deg = Math.round((score / 100) * 360);
		$el.css('background', 'conic-gradient(' + color + ' 0deg ' + deg + 'deg, #e5e7eb ' + deg + 'deg 360deg)');
		$el.text(score + '/100');
	}

	function renderModules(modules) {
		var html = '';
		$.each(modules, function (slug, data) {
			var result = data.result;
			var badgeClass = 'badge-pass';
			if (result.status === 'warning') badgeClass = 'badge-warning';
			else if (result.status === 'fail') badgeClass = 'badge-fail';

			html += '<div class="cx-auditor-module">';
			html += '<h3>' + data.label + ' <span class="badge ' + badgeClass + '">' + result.score + '</span></h3>';
			html += '<table class="widefat striped"><thead><tr><th>Check</th><th>Status</th><th>Value</th></tr></thead><tbody>';
			$.each(result.checks, function (i, check) {
				var statusIcon = '✓';
				if (check.status === 'warning') statusIcon = '⚠';
				else if (check.status === 'fail') statusIcon = '✕';
				var rowClass = 'row-pass';
				if (check.status === 'warning') rowClass = 'row-warning';
				else if (check.status === 'fail') rowClass = 'row-fail';
				html += '<tr class="' + rowClass + '">';
				html += '<td>' + check.label + '</td>';
				html += '<td>' + statusIcon + ' ' + check.status + '</td>';
				html += '<td>' + (check.value || '-') + '</td>';
				html += '</tr>';
			});
			html += '</tbody></table>';
			if (result.recommendations && result.recommendations.length > 0) {
				html += '<div class="cx-auditor-recs"><strong>Recommendations:</strong><ul>';
				$.each(result.recommendations, function (i, rec) {
					html += '<li>' + rec + '</li>';
				});
				html += '</ul></div>';
			}
			html += '</div>';
		});
		$('#cx-auditor-modules').html(html);
	}

	function runAudit() {
		var $btn = $('#cx-auditor-run');
		var $status = $('#cx-auditor-status');
		var site_url = $('#site_url').val();

		if (!site_url) {
			$status.text(CXAuditor.i18n.enterUrl).addClass('error');
			return;
		}

		$btn.prop('disabled', true);
		$status.text(CXAuditor.i18n.scanning).removeClass('error');
		$('#cx-auditor-results').hide();

		$.ajax({
			url: CXAuditor.ajaxUrl,
			method: 'POST',
			data: {
				action: 'cx_auditor_run',
				nonce: CXAuditor.nonce,
				site_url: site_url
			},
			success: function (resp) {
				if (resp.success) {
					currentData = resp.data;
					var $score = $('#cx-auditor-score');
					$score.data('score', currentData.overall);
					renderGauge($score, currentData.overall);
					renderModules(currentData.modules);

					var modulesJson = JSON.stringify(currentData.modules);
					var modulesBase64 = btoa(unescape(encodeURIComponent(modulesJson)));
					var pdfUrl = CXAuditor.adminUrl + '?action=cx_auditor_download_pdf&site_url=' + encodeURIComponent(currentData.site_url) + '&score=' + currentData.overall + '&generated=' + encodeURIComponent(currentData.generated) + '&modules=' + encodeURIComponent(modulesBase64) + '&_wpnonce=' + CXAuditor.pdfNonce;
					$('#cx-auditor-download').attr('href', pdfUrl);

					$('#cx-auditor-results').slideDown();
					$status.text('');
				} else {
					$status.text(resp.data.message || CXAuditor.i18n.error).addClass('error');
					$btn.prop('disabled', false);
				}
			},
			error: function () {
				$status.text(CXAuditor.i18n.error).addClass('error');
				$btn.prop('disabled', false);
			}
		});
	}

	$(document).on('click', '#cx-auditor-run', function(e) {
		e.preventDefault();
		runAudit();
	});
})(jQuery);