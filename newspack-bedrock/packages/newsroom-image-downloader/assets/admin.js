jQuery(document).ready(function($) {
    var $btnScan = $('#btn-scan');
    var $btnDownload = $('#btn-download');
    var $scanStatus = $('#scan-status');
    var $downloadStatus = $('#download-status');
    var $logOutput = $('#log-output');
    var $downloadLog = $('#download-log');

    // Scan
    $btnScan.on('click', function() {
        $btnScan.prop('disabled', true).text(newsroomImgDownloader.strings.scanning);
        $scanStatus.html('<span style="color:#666;">Scanning...</span>');

        $.post(newsroomImgDownloader.ajaxUrl, {
            action: 'newsroom_img_scan',
            _wpnonce: newsroomImgDownloader.nonce,
            post_types: $('#scan-post-types').val()
        }, function(response) {
            $btnScan.prop('disabled', false).text('Scan for External Images');
            if (response.success) {
                var d = response.data;
                $scanStatus.html(
                    '<span style="color:#2e7d32;">Found <strong>' + d.total_images + '</strong> external images in <strong>' + d.posts_with_external + '</strong> posts.</span>'
                );
                // Reload page to show results table
                location.reload();
            } else {
                $scanStatus.html('<span style="color:#c62828;">' + response.data + '</span>');
            }
        }).fail(function() {
            $btnScan.prop('disabled', false).text('Scan for External Images');
            $scanStatus.html('<span style="color:#c62828;">Request failed.</span>');
        });
    });

    // Download all
    $btnDownload.on('click', function() {
        if (!confirm('Download all external images into your media library? This will modify post content.')) return;

        $btnDownload.prop('disabled', true).text(newsroomImgDownloader.strings.downloading);
        $downloadStatus.html('<span style="color:#666;">Downloading...</span>');
        $downloadLog.show();
        $logOutput.html('Starting download...\n');

        $.post(newsroomImgDownloader.ajaxUrl, {
            action: 'newsroom_img_download',
            _wpnonce: newsroomImgDownloader.nonce,
            default_host: $('#default-host').val(),
            dry_run: $('#dry-run').is(':checked') ? 1 : 0
        }, function(response) {
            $btnDownload.prop('disabled', false).text('Download All Images');
            if (response.success) {
                var d = response.data;
                var msg = d.dry_run ? '[DRY RUN] ' : '';
                msg += 'Downloaded: ' + d.downloaded + ' | Errors: ' + d.errors;
                $downloadStatus.html('<span style="color:#2e7d32;">' + msg + '</span>');

                // Show log
                var logHtml = '';
                (d.log || []).forEach(function(entry) {
                    var color = entry.status === 'success' ? '#4ec9b0' : '#f44747';
                    logHtml += '<div style="color:' + color + ';">[' + entry.status.toUpperCase() + '] Post #' + entry.post_id + ': ' + entry.url;
                    if (entry.local) logHtml += ' → ' + entry.local;
                    if (entry.message) logHtml += ' (' + entry.message + ')';
                    logHtml += '</div>';
                });
                $logOutput.html(logHtml);
            } else {
                $downloadStatus.html('<span style="color:#c62828;">' + response.data + '</span>');
            }
        }).fail(function() {
            $btnDownload.prop('disabled', false).text('Download All Images');
            $downloadStatus.html('<span style="color:#c62828;">Request failed.</span>');
        });
    });

    // Download single post
    $(document).on('click', '.btn-download-single', function() {
        var $btn = $(this);
        var postId = $btn.data('post-id');
        $btn.prop('disabled', true).text('...');

        var results = newsroomImgDownloader.scanResults || {};
        var postData = results[postId];
        if (!postData || !postData.images || !postData.images.length) {
            $btn.text('No images');
            return;
        }

        // Download first image only (for quick test)
        $.post(newsroomImgDownloader.ajaxUrl, {
            action: 'newsroom_img_download_single',
            _wpnonce: newsroomImgDownloader.nonce,
            url: postData.images[0].src,
            post_id: postId
        }, function(response) {
            if (response.success) {
                $btn.text('Done').css('color', 'green');
            } else {
                $btn.text('Error').css('color', 'red');
            }
        }).fail(function() {
            $btn.text('Error').css('color', 'red');
        });
    });
});

