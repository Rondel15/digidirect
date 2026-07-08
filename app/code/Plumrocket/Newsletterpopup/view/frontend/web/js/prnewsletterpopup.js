/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

define([
    'jquery',
    'mage/validation',
    'mage/translate',
    'Magento_Customer/js/customer-data',
    'uiRegistry'
], function ($, validation, __, customerData, uiRegistry) {
    'use strict';

    var pjQuery = $; // added for compatibility

    /**
     *
     * @param {{googleTagManagerEnabled: Boolean, activePopupIds: Number, isGlobalCookieUsage: Boolean}} _settings
     */
    window.prnewsletterPopupClass = function (_settings) {
        var loaded = {}
            , locked = {}
            , globalSettings = _settings
            , areaSettings = {
                area: _settings.area,
                w: screen.width,
                referer: document.URL,
                parentReferrer: document.referrer,
                cmsPage: _settings.cmsPage,
                categoryId: _settings.categoryId,
                productId: _settings.productId,
            }
            , firstId = 0
            , prevCursorY = 0
            , _this = this;

        var _dublicateClasses = ['newspopup-blur', 'newspopup_ov_hidden'];

        _this.currentPopupId = 0;

        var _parseArguments = function (args) {
            var result = {
                'id': 0,
                'callback': false
            }

            if (args.length > 1) {
                if (jQuery.isFunction(args[1])) {
                    result.callback = args[1];
                }
                result.id = parseInt(args[0], 10);
            } else if (args.length == 1) {
                if (jQuery.isFunction(args[0])) {
                    result.callback = args[0];
                } else {
                    result.id = parseInt(args[0], 10);
                }
            }
            return result;
        }

        /**
         * Retrieve popups ids from cookies
         *
         * @returns {Number[]}
         */
        this.getLockedPopupIds = function () {
            let cookies = document.cookie.split(';'),
                cookieName = 'prnewsletterpopup_disable_popup',
                ids = [];

            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i].trim();
                if (cookie.startsWith(cookieName)) {
                    const cookieValue = cookie.substring(cookieName.length + 1);
                    const regex = /(\d+)=yes/;
                    const match = cookieValue.match(regex);
                    if (match && match[1]) {
                        ids.push(parseInt(match[1]));
                    }
                }
            }

            return ids;
        }

        /**
         * Check if there is any active popup.
         *
         * @returns {boolean}
         */
        this.hasActivePopups = function () {
            let lockedPopupIds = this.getLockedPopupIds();
            if (lockedPopupIds.length > 0 && globalSettings.isGlobalCookieUsage) {
                return false;
            }

            return globalSettings.activePopupIds.filter(
                popupId => ! lockedPopupIds.includes(popupId)
            ).length > 0;
        }

        this.load = function () {

            if (window.navigator
                && window.navigator.userAgent
                && window.navigator.userAgent.match('/bot|crawl|slurp|spider/i')
            ) {
                return;
            }

            var data = {};
            var args = _parseArguments(arguments);

            if (args.id > 0) {
                if (args.id in loaded) {
                    if (args.callback) {
                        args.callback(true, false);
                    }
                    return false;
                }
                data['id'] = args.id;
            }

            // copy area settings
            for (var k in areaSettings) {
                data[k] = areaSettings[k];
            }

            data.is_preview = globalSettings.is_preview;

            $.ajax({
                type:       'POST', /* need to be post for Varnish & FPC compatibility */
                url:        globalSettings.block_url,
                global:     false,
                dataType:   'html',
                data:       data
            })
                .done(function (responseData, statusText, xhr ) {
                    _afterLoad(statusText, responseData, args.callback);
                })
                .fail(function (xhr, statusText) {
                    _afterLoad(statusText, '', args.callback);
                });

            initTwoStepPopupListener();

            return true;
        }

        var _afterLoad = function (status, html, callback) {
            var succ = false;
            if (status === 'success' && html) {
                // support old templates
                if ($('#newspopup_up_bg').length > 0) {
                    $('#newspopup_up_bg').remove();
                    firstId = 0;
                }
                $('body').prepend(html);
                succ = true;
            }

            if (callback) {
                callback(succ, true);
            }
        }

        if (areaSettings.area !== 'account' && this.hasActivePopups()) {
            this.load();
        }

        /**
         * @param {{
         *      display_popup: String,
         *      delay_time: Number,
         *      mobile_leave_delay_time: Number,
         *      page_scroll:Number,
         *      css_selector: String,
         *      success_url:String,
         *      cookie_time_frame:Number,
         *      id:Number,
         *      current_device:String
         * }} settings
         */
        this.updateSettings = function (settings) {
            if (firstId == 0) {
                firstId = settings.id;
            }

            // If current device is Tablet or Mobile, leave_page change to after_time_delay.
            if (settings.display_popup === 'leave_page'
                && (settings.current_device === 'tablet' || settings.current_device === 'mobile')
            ) {
                settings.display_popup = 'after_time_delay';
                settings.delay_time = settings.mobile_leave_delay_time;
            } else if (settings.display_popup === 'manually' && globalSettings.is_preview) {
                settings.display_popup = 'after_time_delay';
                settings.delay_time = 0;
            }

            // Switch beetwen modes
            switch (settings.display_popup) {
                case 'after_time_delay':
                    setTimeout(function () {
                        _this.show(settings.id);
                    }, settings.delay_time * 1000);
                    break;
                case 'leave_page':
                    var actMouseOut = true;
                    (function (obj, evt, fn) {
                        if (! actMouseOut) {
                            return;
                        }
                        if (obj.addEventListener) {
                            obj.addEventListener(evt, fn, false);
                        } else if (obj.attachEvent) {
                            obj.attachEvent("on" + evt, fn);
                        }
                    }) (document, "mouseout", function (e) {
                        if (! actMouseOut) {
                            return;
                        }
                        e = e ? e : window.event;
                        var from = e.relatedTarget || e.toElement;
                        var cursorY = e.pageY - jQuery(document).scrollTop();
                        if ((cursorY < 0 && prevCursorY < 300) && (!from || from.nodeName === 'HTML')) {
                            _this.show(settings.id);
                            actMouseOut = false;
                        }
                        prevCursorY = cursorY;
                    });
                    break;
                case 'on_page_scroll':
                    var actScroll = true;
                    $(window).on('scroll', function () {
                        if (!actScroll) {
                            return;
                        }
                        if ($(window).scrollTop() / ($(document).height() - $(window).height()) >= settings.page_scroll / 100 ) {
                            _this.show(settings.id);
                            actScroll = false;
                        }
                    });
                    break;
                case 'on_mouseover':
                    $('body').on('mouseover.np tap.np', settings.css_selector, function () {
                        _this.show(settings.id);
                        $('body').off('mouseover.np tap.np');
                    });
                    break;
                case 'on_click':
                    var always = false;
                    $('body').on('mousedown.np', settings.css_selector, function (e) {
                        _this.show(settings.id);
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        e.preventDefault();
                        if (!always) {
                            $('body').off('mousedown.np');
                        }
                        return false;
                    });
                    break;
                case 'manually':
                    //bindPopupLogin('a, button');
                    break;
            }

            var $popupSuccess = $('.newspopup-message-success');
            var $popup = $('#newspopup_up_bg_' + settings.id);
            // Convert to new template system
            if (($popup.length == 0) && (settings.id == firstId)) {
                $popup = $('#newspopup_up_bg').addClass('newspopup_up_bg').attr('id', '#newspopup_up_bg_' + settings.id);
            }
            var olds = ['newspopup_up_bg_form', 'newspopup-animated-form', 'prpop-addedoverlay', 'newspopup-messages-holder'];
            for (var i = 0, len = olds.length; i < len; i++) {
                $popup.find('#' + olds[i]).addClass(olds[i]).removeAttr('id');
            }

            // Prepare related popups.
            $popup.find('*[data-npid]').on('click', function () {
                var popupId = $(this).data('npid');
                if (popupId > 0) {
                    prnewsletterPopup.show(popupId);
                    var npaction = $(this).data('npaction')? $(this).data('npaction') : '"Switched Popup #' + settings.id + ' to #' + popupId;
                    send(globalSettings.history_url, 'Confirmed', function(data) { return (data +'&'+ jQuery.param({'npaction': npaction})); }, function(m, a) {});
                }
                return false;
            })
            .each(function () {
                var popupId = $(this).data('npid');
                if (popupId > 0) {
                    prnewsletterPopup.load(popupId);
                }
            });

            $popup.find('*[data-npaction="Cancel"]').on('click', function () {
                popupClose();
                send(globalSettings.cancel_url, 'Cancel', function(data) { return data; }, function(m, a) {});
                return false;
            });

            // --- end ---
            var $messagesHolder    = $popup.find('.newspopup-messages-holder-tr');
            var $form             = $popup.find('form');

            loaded[ settings.id ] = $popup;

            var prepareSendData = function (loadFunction) {
                // copy area settings
                var data = {'id': settings.id}
                for (var k in areaSettings) {
                    data[k] = areaSettings[k];
                }
                data = loadFunction(jQuery.param(data))
                return data;
            }

            var send = function (url, action, loadFunction, finalFunction) {
                if (action === 'Cancel' && globalSettings.is_preview) {
                    return;
                }

                $form.find('.ajax-loader').show();
                $messagesHolder.empty();
                eventTracking(action, 'Send request', settings.id);
                blockForm(settings.id);
                _this.isSubscribed = false;

                $.ajax({
                    type:         'POST',
                    url:           url,
                    dataType:     'json',
                    data:         prepareSendData(loadFunction)
                })
                .done(function (responseData, statusText, xhr ) {
                    $form.find('.ajax-loader').hide();
                    if (statusText === 'success') {
                        if (responseData.error == 0) {
                            if (areaSettings.area !== 'account') {
                                setCookieForDisable();
                            }
                            eventTracking(action, 'Success', settings.id);
                            finalFunction(responseData.messages, action, responseData.hasSuccessTextPlaceholders);
                        } else {
                            showMessages(responseData.messages, action);
                        }
                    }
                })
                .always(function () {
                    unblockForm(settings.id);
                    $form.find('.ajax-loader').hide();
                })
                .fail(function () {
                    unblockForm(settings.id);
                });
            }

            $form.submit(function () {
                if (isFormBlocked(settings.id)) {
                    return false;
                }

                // validation
                /*var validator  = new Validation( $form.get(0) );
                if (validator && validator.validate()) {} else {
                    jQuery('.validation-advice').mouseenter(function(){ jQuery(this).fadeOut(); });
                    return false;
                }*/

                if ($($form.get(0)).validation() && $($form.get(0)).validation('isValid')) {
                    send(globalSettings.action_url, 'Subscribe', function (data) {
                        return data + '&' + $form.serialize();
                    }, function (messages, action, hasSuccessTextPlaceholders) {
                        _this.isSubscribed = true;
                        $popupSuccess.find('.newspopup-message-content').html(messages.success);
                        showMessages(messages, action);
                        // can contain just success or nothing
                        if (messages && hasSuccessTextPlaceholders) {
                            $popupSuccess.find('.newspopup-message-close').on('click', function() {
                                popupRedirect(settings.success_url);
                            });
                        } else if (messages && ! hasSuccessTextPlaceholders) {
                            setTimeout(function(){
                                popupClose();
                                popupRedirect(settings.success_url);
                            }, 5000);
                        } else {
                            popupClose();
                            popupRedirect(settings.success_url);
                        }
                    });
                } else {
                    jQuery('.newspopup_up_bg div.mage-error').mouseenter(function() { jQuery(this).fadeOut(); });
                }

                return false;
            });

            $popup.find('.send').click(function () {
                $form.submit();
                return false;
            });

            // Close button.
            $popup.find('.close').click(function () {
                if (! isFormBlocked(settings.id)) {
                    popupClose();
                    if(!_this.isSubscribed) {
                        send(globalSettings.cancel_url, 'Cancel', function(data) { return data; }, function(m, a) {});
                    }
                }
                return false;
            });

            if (!settings.isWidget) {
                // Close if click to background.
                $('#newspopup_up_bg_'+settings.id).on('click', function(e) {
                    if(e.target != this || window.nsStopClose) return;
                    if (! isFormBlocked(settings.id)) {
                        popupClose();
                        if(!_this.isSubscribed) {
                            send(globalSettings.cancel_url, 'Cancel', function(data) { return data; }, function(m, a) {});
                        }
                    }
                });

                // Close if press esc-button.
                $(document).keydown(function(e) {
                    if (settings.id != _this.currentPopupId) return;

                    var code = e.keyCode? e.keyCode : e.which;
                    if (code === 27) {
                        // esc
                        if (! isFormBlocked(settings.id)) {
                            popupClose();
                            if(!_this.isSubscribed) {
                                send(globalSettings.cancel_url, 'Cancel', function(data) { return data; }, function(m, a) {});
                            }
                        }
                    }
                });
            }

            $('.newspopup-message-success .newspopup-message-close').on('click', function () {
                popupClose();

                // popupClose not working for widget popup, so close it manually
                if ($popup.hasClass('pr-mode-form')) {
                    $popup.hide();
                }

                popupRedirect(settings.success_url);
            });

            var popupClose = function () {
                for (var i=0; i<_dublicateClasses.length; i++) {
                    var cl = _dublicateClasses[i]+'-'+_this.currentPopupId;
                    $('.'+cl).removeClass(cl);
                }
                _this.currentPopupId = 0;

                $('.page-wrapper,#wrapper,#wrap,.wrapper').removeClass('newspopup-blur');

                $('body').removeClass('newspopup_ov_hidden');
                // Don't hide success message for widget template. Let it for button "Ok"
                if (! $popup.hasClass('pr-mode-form')) {
                    $popup.hide();
                }

                setCookieForDisable();
            }

            var setCookieForDisable = function () {

                if (globalSettings.is_preview) {
                    return;
                }

                var seconds = (settings.cookie_time_frame > 0)
                    ? settings.cookie_time_frame
                    : 3650 * 86400; // emulate never expire
                window.setNsCookie('prnewsletterpopup_disable_popup_' + settings.id, 'yes', {
                    expires: seconds,
                    path: '/'
                });
            }

            var showMessages = function (messages, action) {

                if (messages.success || !messages.error) {
                    $popup.find('.newspopup-theme').hide();
                    $popup.find('.newspopup-message-success').eq(0).show();
                    return;
                }

                if ($(messages).length) {
                    for (var _type in messages) {
                        for (var i = 0, len = messages[_type].length; i < len; i++) {
                            renderMessage(messages[_type][i], _type, $messagesHolder);
                            if (_type == 'error') {
                                eventTracking(action, 'Error: ' + messages[_type][i], settings.id);
                            }
                        }
                    }
                }
            }
        }

        this.show = function () {
            var id = firstId;
            var succ = false;

            var args = _parseArguments(arguments);
            if (args.id > 0) {
                id = args.id;
            }

            if (id in loaded && $('#newspopup_up_bg_'+id+' .newspopup-up-form').is(':visible') == false) {
                $('#newspopup_up_bg_'+id+' .newspopup-up-form').show();
                $('#newspopup_up_bg_'+id+' .newspopup-message-success').hide();

                $('.newspopup_up_bg, #newspopup_up_bg').hide(0);
                // $('body').css('overflow-y', 'hidden');
                $('body').addClass('newspopup_ov_hidden');
                $('.page-wrapper,#wrapper,#wrap,.wrapper').addClass('newspopup-blur');
                loaded[id].show();

                // Activate knockout components, for example Data Privacy checkboxes
                var triggerResult = $('.prgdpr-consent-checkboxes-block', loaded[id]);
                // if we use popup and static form on same page, this code can runs twice
                if (typeof triggerResult.applyBindings === 'function') {
                    triggerResult.applyBindings();
                }

                // Activate knockout components, for Magento 2.4 ReCaptcha
                loaded[id].find('.field-recaptcha').each(function( index ) {
                    let bindValue = $(this).data('bind').replace("scope:", "").replace("'", ""),
                        reCaptchaId = /scope:'(.*)'/.exec(bindValue);
                    uiRegistry.remove(reCaptchaId);
                    $(this).parent().trigger('contentUpdated').applyBindings();
                });

                succ = true;
                window.nsStopClose = true;
                setTimeout(function () {
                    window.nsStopClose = false;
                }, 2000);

                _this.currentPopupId = id;
                for (var i=0; i<_dublicateClasses.length; i++) {
                    $('.'+_dublicateClasses[i]).addClass(_dublicateClasses[i]+'-'+id);
                }
            }

            if (args.callback) {
                args.callback(succ);
            }

            if (globalSettings.is_preview && loaded[id]) {
                var messageElement = document.querySelector('.newspopup-preview-notice');
                if (messageElement) {
                    var iconNotice = '<span><svg height="25px" width="25px" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve"><g><polygon fill="#ffffff" points="55.9,68.6 61.2,4.8 36.3,4.8 41.6,68.6  "></polygon><rect x="39.4" y="76" fill="#ffffff" width="18.8" height="18.8"></rect></g></svg></span>';
                    renderMessage(
                        iconNotice + messageElement.innerHTML,
                        'preview-notice',
                        loaded[id].find('.newspopup-messages-holder-tr')
                    );
                    messageElement.remove();
                }
            }

            /**
             * Some of popups has special styles for one field,
             * e.g. buttons might be on the same line as input, see theme "24. Amazing Shapes"
             */
            var popupFields = $('.pr-np-fields-wrapper > li');
            if (popupFields.length === 1) {
                $('.newspopup_up_bg_form-wrap').addClass('pr-np-one-field');
            }
            // for bind on <a>
            return false;
        }

        var eventTracking = function (action, label, popupId) {
            if (globalSettings.enable_analytics) {
                if (typeof ga !== 'undefined' && ga !== false) {
                    ga('send', 'event', 'Newsletter Popup ' + popupId, action, label.replace(/(<([^>]+)>)/ig, ''));
                } else if (typeof _gaq !== 'undefined' && _gaq !== false) {
                    _gaq.push(['_trackEvent', 'Newsletter Popup ' + popupId, action, label.replace(/(<([^>]+)>)/ig, '')]);
                }
            }

            if (globalSettings.googleTagManagerEnabled) {
                window.dataLayer = window.dataLayer || [];
                dataLayer.push({
                    'event':'newsletter_popup',
                    'popup_id': popupId,
                    'popup_label': label.replace(/(<([^>]+)>)/ig, ''),
                    'action': action
                });
            }
        }

        var popupRedirect = function (success_url) {
            customerData.invalidate(["*", "messages"]);
            if (success_url) {
                window.location.href = success_url;
            }
        }

        var blockForm = function (id) {
            locked[id] = true;
        }

        var unblockForm = function (id) {
            delete locked[id];
        }

        var isFormBlocked = function (id) {
            return (id in locked) && locked[id];
        }

        var renderMessage = function (message, type, $messagesHolder) {
            var text = message + ' <a style="float: right;" onclick="jQuery(this).parent().hide().empty(); return false;" href="#">'+ __('Close') +'</a>';
            $('<div></div>').addClass(type).html(text).appendTo($messagesHolder);
        };

        function showSecondaryPopup(parent)
        {
            if (parent) {
                var
                    firstPopup = parent.querySelector('.first_popup'),
                    secondPopup = parent.querySelector('.secondary_popup');

                if (firstPopup && secondPopup) {
                    firstPopup.classList.add('hidden');
                    secondPopup.classList.remove('hidden');
                } else {
                    console.warn('Not found elements with class "first_popup" or "secondary_popup"');
                }
            }
        }

        function initTwoStepPopupListener()
        {
            document.body.addEventListener('click', function (e) {
                if (e.target.classList.contains('show_secondary_popup')) {
                    var popupWrapper = e.target.closest('.pr-newsletter-popup__wrp');
                    showSecondaryPopup(popupWrapper);
                }
            })
        }
    }

    // -----------------------------------------------------

    window.setNsCookie = function (name, value, options) {
        options = options || {};

        var expires = options.expires;

        if (typeof expires == "number" && expires) {
            var d = new Date();
            d.setTime(d.getTime() + expires*1000);
            expires = options.expires = d;
        }
        if (expires && expires.toUTCString) {
            options.expires = expires.toUTCString();
        }

        value = encodeURIComponent(value);
        var updatedCookie = name + "=" + value;

        for (var propName in options) {
            updatedCookie += "; " + propName;
            var propValue = options[propName];
            if (propValue !== true) {
                updatedCookie += "=" + propValue;
            }
        }

        document.cookie = updatedCookie;
    }
});
