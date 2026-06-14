/* global jQuery */
(function ($) {
    'use strict';

    $(function () {

        /* ── Colour picker ──────────────────────────────────────── */
        $('#popyAccent').wpColorPicker({ change: debounce(syncPreview, 150), clear: debounce(syncPreview, 150) });

        /* ── Live sync ──────────────────────────────────────────── */
        function syncPreview() {
            var accent = $('#popyAccent').val() || '#1e4d3b';

            // Text fields
            $('#popyPrevIcon').text(    $('#popyIcon').val() );
            $('#popyPrevEyebrow').text( $('#popyEyebrow').val() ).css('color', accent);
            $('#popyPrevTitle').text(   $('#popyTitle').val() );
            $('#popyPrevSubtitle').html( $('#popySubtitle').val() );
            $('#popyPrevBody').html(     $('#popyBody').val() );
            $('#popyPrevFootnote').text( $('#popyFootnote').val() );
            $('#popyPrevPrimary').text(  $('#popyPrimaryText').val() ).css('background', accent);
            $('#popyPrevSecondary').text($('#popySecondaryText').val() );
            $('#popyPrevDismiss').text(  $('#popyDismissText').val() );
        }

        // Debounce
        function debounce(fn, ms) {
            var t; return function () { clearTimeout(t); t = setTimeout(fn, ms); };
        }

        // Bind all text inputs
        $('input[id^="popy"]').not('#popyAccent').on('input', debounce(syncPreview, 180));

        // Initial render
        syncPreview();

        /* ── Reset cookie ───────────────────────────────────────── */
        $('#popyResetCookie').on('click', function () {
            document.cookie = 'popy_dismissed=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            var $b = $(this), orig = $b.text();
            $b.text('✅ Done! Reload your site to see the popup.').prop('disabled', true);
            setTimeout(function () { $b.text(orig).prop('disabled', false); }, 3500);
        });

    });

}(jQuery));
