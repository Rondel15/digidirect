/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

require([
    'jquery',
    'mage/translate',
    'Magento_Variable/variables',
    'mage/adminhtml/events',
    'mage/adminhtml/browser',
    'mage/backend/tabs',
    'Plumrocket_Newsletterpopup/js/codemirror',
    'Plumrocket_Newsletterpopup/js/event/simulate',
    'domReady!'
], function (pjQuery, __) {
    'use strict';

    pjQuery('#popup_success_page option[value=__none__]').attr('disabled', 'disabled');

    var oOptions = {
        method: "POST",
        parameters: Form.serialize("edit_form"),
        asynchronous: true,
        onFailure: function (oXHR) {
        },
        onSuccess: function (oXHR) {
            var x = window.open(window.prnewsletterpopupOptions.previewUrl, '_blank');
            x.document.open();
            x.document.write(oXHR.responseText);
            x.document.close();
        }
    };

    window.previewPopup = function () {
        /*we stop the default submit behaviour*/
        if ('tinymce' in window) {
            var obj = tinymce.get('popup_text_description');
            if (obj) {
                pjQuery('#popup_text_description').val(obj.getContent());
            }

            var obj = tinymce.get('popup_text_success');
            if (obj) {
                pjQuery('#popup_text_success').val(obj.getContent());
            }
        }

        prepareCodeAndStyle();
        var oRequest = new Ajax.Updater({success: oOptions.onSuccess.bindAsEventListener(oOptions)}, window.prnewsletterpopupOptions.previewUrl, oOptions);
    };

    window.previewTemplate = function () {
        prepareCodeAndStyle();
        var oRequest = new Ajax.Updater({success: oOptions.onSuccess.bindAsEventListener(oOptions)}, window.prnewsletterpopupOptions.previewUrl, oOptions);
    };

    var getSelectionStart = function (editor) {

        var start = 0;
        var cursor = editor.getCursor();
        var line = cursor.line;
        var offset = cursor.ch;
        var lines = editor.lineCount();
        var i = 0;
        for (i = 0; i < lines; i++) {
            if (i == line) {
                start += offset;
                return start;
            }
            start += editor.lineInfo(i).text.length + 1;
        }
        return start;
    };

    window.cmSyncSelectionByEditor = function (textarea, editor) {

        var pos = getSelectionStart(editor);
        pjQuery(textarea).attr('disabled', false);
        pjQuery(textarea).prop('selectionStart', pos);
        pjQuery(textarea).prop('selectionEnd', pos);
    };

    window.cmSyncChangesByTextarea = function (textarea, editor) {
        pjQuery(textarea).attr('disabled', false);
        editor.setValue(pjQuery(textarea).val());
        editor.refresh();
    };

    window.cmSyncChangesByEditor = function (textarea, editor) {
        pjQuery(textarea).attr('disabled', false);
        pjQuery(textarea).val(editor.getValue());
        editor.refresh();
    };

    var prepareCodeAndStyle = function () {
        pjQuery('#edit_form .base64_hidden').remove();
        pjQuery('#edit_form').append(pjQuery('<input type="hidden" class="base64_hidden">').attr('name','code_base64').val(Base64.encode(pjQuery('#template_code').val())));
        pjQuery('#edit_form').append(pjQuery('<input type="hidden" class="base64_hidden">').attr('name','style_base64').val(Base64.encode(pjQuery('#template_style').val())));
        if (pjQuery('#template_code').val() != '') {
            pjQuery('#template_code').attr('disabled', true);
        }
        if (pjQuery('#template_style').val() != '') {
            pjQuery('#template_style').attr('disabled', true);
        }
    };

    /*Initialization.*/
    pjQuery('#popup_coupon_code').change(function () {
        pjQuery.ajax({
            url: window.prnewsletterpopupOptions.couponDataController,
            type: 'GET',
            dataType: 'json',
            data: {ruleId: pjQuery('#popup_coupon_code').val()},
            context: $('#customer-reviews-box'),
            success: function (data) {
                if (data) {
                    var options = data;
                    var id = pjQuery('#popup_coupon_code').val();

                    if (!pjQuery('#popup_start_date').val() && !pjQuery('#popup_end_date').val()) {
                        pjQuery('#popup_start_date').val(options.from_date);
                        pjQuery('#popup_end_date').val(options.to_date);
                    }

                    if (parseInt(id) > 0) {
                        pjQuery('#popup_code_container').hide();
                        pjQuery('#popup_coupon_fieldset').find('input, select').removeAttr('disabled');
                    } else {
                        pjQuery('#popup_code_container').show();
                        pjQuery('#popup_coupon_fieldset').find('input, select').attr('disabled', 'disabled');
                    }

                    var errorText = __('This option is only available for shopping cart rules with auto generated coupons');
                    var className = 'extended-time-message';
                    var cetContainer = pjQuery('#popup_coupon_expiration_time_day').parent('div');

                    cetContainer.find('.' + className).remove();

                    if (!options.use_auto_generation && (id != 0)) {
                        var messageHtml = '<span class="' + className + '">'
                            + errorText
                            + '</span>';

                        cetContainer.prepend(messageHtml);
                    }
                }
            }.bind(this),
            showLoader: true,
            dontHide: false
        });
    });

    pjQuery('#popup_coupon_code').trigger('change');

    var _checkEnable = function () {
        var $chk = pjQuery(this);
        if (! $chk.is(':checked')) {
            $chk.parents('tr').addClass('not-active');
        } else {
            $chk.parents('tr').removeClass('not-active');
        }
    };

    pjQuery('#popup_signup_fieldset table.data-grid tbody input.checkbox').click(_checkEnable).each(_checkEnable);

    varienGlobalEvents.attachEventHandler('formSubmit', function () {
        if (typeof codeEditor != 'undefined') {
            cmSyncChangesByEditor('#template_code', codeEditor);
        }
        if (typeof styleEditor != 'undefined') {
            cmSyncChangesByEditor('#template_style', styleEditor);
        }

        prepareCodeAndStyle();
    });

    pjQuery('#edit_tabs').on('tabscreate tabsactivate', function () {
        if (typeof codeEditor != 'undefined') {
            codeEditor.refresh();
        }
        if (typeof styleEditor != 'undefined') {
            styleEditor.refresh();
        }
    });

    pjQuery('#choose_template,#template_id_picker .template-current').on('click', function (e) {
        pjQuery('#template_id_picker .template-list').toggle();
        e.stopPropagation();
        e.preventDefault();
    });

    pjQuery('#template_id_picker').on('click', 'li .list-table-td,li button.select_template', function () {
        var $el = pjQuery(this).parents('li');
        pjQuery('#template_id_picker li').removeClass('active');
        $el.addClass('active');
        pjQuery('#popup_template_id').val($el.data('id'));
        pjQuery('#template_id_picker .template-current').html($el.html());

        /*pjQuery('#loading-mask').show();*/
        new Ajax.Request(pjQuery('#template_id_picker').data('action'), {
            method: "get",
            parameters: {'id': $el.data('id')},

            onSuccess: function successFunc(transport)
            {
                var data = transport.responseText.evalJSON();
                if (data.code || data.style) {
                    codeEditor.setValue(data.code);
                    cmSyncChangesByEditor('#template_code', codeEditor);
                    styleEditor.setValue(data.style);
                    cmSyncChangesByEditor('#template_style', styleEditor);
                }

                for (var i in data) {
                    var $editor = tinyMCE.get('popup_'+ i);
                    if ($editor != undefined) {
                        $editor.setContent(data[i]? data[i] : '');
                        continue;
                    }
                    var $field = pjQuery('#edit_tabs_labels_section_content #popup_' + i + ',#edit_tabs_display_section_content #popup_'+ i);
                    if ($field.length) {
                        $field.val(data[i]);
                    }
                }

                if (data.signup_fields) {
                    var $fieldsArea = pjQuery('#popup_signup_fieldset table.data-grid tbody tr');

                    $fieldsArea.find('input[type=checkbox][name!="signup_fields[email][enable]"]').prop('checked', false);

                    for (var field in data.signup_fields) {
                        $fieldsArea.find('input[name="signup_fields['+ field +'][enable]"]').prop('checked', data.signup_fields[field]['enable']);
                        $fieldsArea.find('input[name="signup_fields['+ field +'][label]"]').val(data.signup_fields[field]['label']);
                        $fieldsArea.find('input[name="signup_fields['+ field +'][sort_order]"]').val(data.signup_fields[field]['sort_order']);
                    }

                    pjQuery('#popup_signup_fieldset table.data-grid tbody input.checkbox').each(_checkEnable);
                    /*pjQuery('#popup_mailchimp_fieldset table.data-grid tbody input.checkbox').each(_checkEnable);*/
                }
            },
            onFailure:  function () {},
            onComplete: function () {
                pjQuery('#template_id_picker .template-list').hide();
                Event.simulate('popup_template_id', 'change');
            }
        });
    })
    .on('click', '.template-expand', function () {
        var $btn = pjQuery(this);
        var $list = $btn.parent().next('.template-wrapper').find('ul');
        var $shadow = $btn.parent().next('.template-wrapper').find('.shadow');
        $btn.toggleClass('template-minify');
        $list.toggleClass('expand-all');
        $shadow.toggleClass('shadow-hide');
    })
    .find('li[data-id='+ pjQuery('#popup_template_id').val() +']').addClass('active');

    var _templateCurrentHtml = pjQuery('#template_id_picker li[data-id='+ pjQuery('#popup_template_id').val() +']').html();
    if (_templateCurrentHtml) {
        pjQuery('#template_id_picker .template-current').empty().html(_templateCurrentHtml);
    }

    pjQuery('#template_id_picker .template-list').on('click', function (e) {
        window.showTemplatePickerList = true;
    });

    pjQuery('html').on('click', function (e) {
        if (pjQuery('#template_id_picker .template-list').is(':visible') && e.target != pjQuery('#template_id_picker .template-list')[0] && pjQuery(e.target).parents('#template_id_picker .template-list')[0] != pjQuery('#template_id_picker .template-list')[0]) {
            if (!window.showTemplatePickerList) {
                pjQuery('#template_id_picker .template-list').hide();
            }
            window.showTemplatePickerList = false;
        }
    });

    if (window.prnewsletterpopupOptions.successTextPlaceholders) {
        var oldOpenChooser = window.MagentovariablePlugin.openChooser;
        var sPlaceholders = window.prnewsletterpopupOptions.successTextPlaceholders;
        var label = sPlaceholders.label;
        var values = sPlaceholders.values;
        var variablesHtml = '<li><b>' + __('Newsletter Popup Variables') + '</b></li>';

        for (var i=0;i<values.length;i++) {
            var onClickScript = 'MagentovariablePlugin.insertVariable(\'' + values[i].value + '\');return false;';
            variablesHtml += '<li><a href="#" onclick="' + onClickScript + '">' + values[i].label + '</a></li>';
        }

        window.MagentovariablePlugin.openChooser = function (variables) {
            oldOpenChooser(variables);

            pjQuery('#' + window.Variables.dialogWindowId).find('ul').prepend(variablesHtml);
        };
    }

    prepareIntegrationTab();

    function prepareIntegrationTab() {
        var integrationTabContentEl = pjQuery('#edit_tabs_integration_section_content'),
            inactiveEntryEditEl = pjQuery('#popup_inactive_fieldset').parent('.entry-edit');

        if (integrationTabContentEl.find('fieldset').length > 1) {
            inactiveEntryEditEl.hide();
        }

        /* Prepare each integration fieldset */
        integrationTabContentEl.find('fieldset').each(function () {
            if ('popup_inactive_fieldset' !== this.id) {
                reinitializeIntegrationFieldset(this);
            }
        });

        var formElementsSelector = 'select.integration-enable';
            formElementsSelector += ', select.integration-mode';
            formElementsSelector += ', .list-enable-column input[type=checkbox]';

        /* Add listeners for each form elements for reinitialize current integration fieldset */
        pjQuery(formElementsSelector).on('change', function() {
            var currentFieldset = pjQuery(this).parents('fieldset');
            reinitializeIntegrationFieldset(currentFieldset);
        });

        integrationTabContentEl.find('table.data-grid tbody input.checkbox').click(_checkEnable).each(_checkEnable);
    }

    function reinitializeIntegrationFieldset(fieldset)
    {
        var fieldsetEl = pjQuery(fieldset),
            enableEl = fieldsetEl.find('select.integration-enable'),
            modeEl = fieldsetEl.find('select.integration-mode'),
            listEl = fieldsetEl.find('table.data-grid');

        if (enableEl.length) {
            var canShowDependElements = 1 === parseInt(enableEl.val());
            modeEl.parents('.admin__field').toggle(canShowDependElements);
            listEl.parents('.admin__field').toggle(canShowDependElements);
        }

        if (modeEl.length) {
            var mode = modeEl.val(),
                noticeEl = fieldsetEl.find('#popup_code_container'),
                hasCheckedLists = listEl.find('.list-enable-column input[type=checkbox]:checked').length > 0;

            if (noticeEl.length) {
                var canShowNotice = mode !== 'all' && ! hasCheckedLists;

                if (enableEl.length && (0 === parseInt(enableEl.val()))) {
                    canShowNotice = false;
                }

                noticeEl.toggle(canShowNotice);
            }
        }
    }

    if(document.querySelector('.prnewsletterpopup-popups-edit')) {
        var $typeNote = pjQuery('#type-note');
        var popupTypeModalSwitcher = document.getElementById('popup_typemodal');
        var $popupDelayTimeField = pjQuery('#popup_delay_time');

        if (popupTypeModalSwitcher.checked) {
            $typeNote.hide();
        } else {
            // disabling field by js because not working from php
            $popupDelayTimeField.prop('disabled', true);
        }
        pjQuery('input[name="type"]').change(function (jQueryEvent) {
            if (jQueryEvent.target.value === 'modal') {
                $typeNote.hide();
            } else {
                $typeNote.show();
            }
        });
    }
});
