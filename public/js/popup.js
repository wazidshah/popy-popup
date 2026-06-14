/* global popyConfig, jQuery */
(function ($) {
    'use strict';

    var cfg          = window.popyConfig || {};
    var DELAY        = cfg.delay        || 10000;
    var DAYS         = cfg.cookieDays   || 7;
    var OV_CLOSE     = cfg.overlayClose !== false;
    var COOKIE       = 'popy_dismissed';

    function getCookie(n) {
        var m = document.cookie.match('(?:^|; )' + encodeURIComponent(n) + '=([^;]*)');
        return m ? decodeURIComponent(m[1]) : null;
    }

    function setCookie(n, v, days) {
        var exp = '';
        if (days > 0) {
            var d = new Date();
            d.setTime(d.getTime() + days * 864e5);
            exp = '; expires=' + d.toUTCString();
        }
        document.cookie = encodeURIComponent(n) + '=' + encodeURIComponent(v) + exp + '; path=/; SameSite=Lax';
    }

    var $ov  = $('#popyOverlay');
    var open = false;

    function show() {
        if (!$ov.length || open) return;
        open = true;
        $ov.css('display', 'flex');
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                $ov.addClass('popy-in');
            });
        });
        setTimeout(function () { $('#popyClose').trigger('focus'); }, 380);
    }

    function hide() {
        $ov.removeClass('popy-in');
        open = false;
        setCookie(COOKIE, '1', DAYS);
        setTimeout(function () { $ov.css('display', 'none'); }, 380);
    }

    $(function () {
        if (!$ov.length)                  return;
        if (getCookie(COOKIE) === '1')    return;

        setTimeout(show, DELAY);

        $(document).on('click', '#popyClose, #popyDismiss', function (e) {
            e.preventDefault();
            hide();
        });

        if (OV_CLOSE) {
            $ov.on('click', function (e) {
                if ($(e.target).is($ov)) hide();
            });
        }

        $(document).on('keydown', function (e) {
            if ((e.key === 'Escape' || e.keyCode === 27) && open) hide();
        });
    });

}(jQuery));
