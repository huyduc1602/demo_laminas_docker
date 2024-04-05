(function($) {
    var invalidTxt = '正しくご入力ください。',
        recentryForm = $('#rec-entry__form'),
        validItems = {
            first_name: false,
            last_name: false,
            rec_sei: false,
            rec_mei: false,
            year_of_birth: false,
            month_of_birth: false,
            day_of_birth: false,
            age: false,
            zipcode: false,
            address: false,
            rec_phone: false,
            rec_email: false,
            experience: false
        }

    isValidName = function(str) {
            if (str) {
                var isMatch = str.match(/[^\-\[\]\_a-zA-Z0-9\s\u3000\u3040-\u30ff\u3400-\u4dbf\u4e00-\u9fff\uf900-\ufaff\uff66-\uff9f]/);
                return null === isMatch;
            }
            return false;
        },

        evetError = function(evt) {
            var self = $(this),
                thisVal = (self.val() || '').trim();
            if (thisVal !== '') {
                if (self.attr('aria-describedby')) self.parent().find('.warning-box').css('display', 'none');
                self.removeClass('not-null');
            } else self.one('keyup', evetError);
        },
        evtValidInput = function(element, vals) {
            element.attr('data-original-title', invalidTxt);
            if ('' === vals) {
                element.one('keyup', evetError).addClass('not-null');
                if (!element.attr('aria-describedby')) element.parent().find('.warning-box')
                    .css('display', 'block')
                    .find('.warning-txt').text(invalidTxt);
                return false;
            }
            return true;
        },

        evtValidInputEmail = function(element, vals) {
            const emailRegex = /^([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})$/i;
            element.attr('data-original-title', invalidTxt);
            if (!emailRegex.test(vals)) {
                element.one('keyup', evetError).addClass('not-null');
                if (!element.attr('aria-describedby')) element.parent().find('.warning-box')
                    .css('display', 'block')
                    .find('.warning-txt').text(invalidTxt);
                return false;
            }
            return true;
        }
    evtValidInputPhone = function(element, vals) {
        element.attr('data-original-title', invalidTxt);
        if (vals.length < 10) {
            element.one('keyup', evetError).addClass('not-null');
            if (!element.attr('aria-describedby')) element.parent().find('.warning-box')
                .css('display', 'block')
                .find('.warning-txt').text(invalidTxt);
            return false;
        }
        return true;
    }

    evtValidSelectOption = function(element, vals) {
        element.attr('data-original-title', invalidTxt);
        if ('' === vals) {
            element.addClass('not-null').parent().find('.warning-box')
                .css('display', 'block')
                .find('.warning-txt').text(invalidTxt);
            return false
        }
        return true;
    }

    evtInputZipCode = function(element, vals) {
        element.attr('data-original-title', invalidTxt);
        if (vals.length !== 7) {
            element.one('keyup', evetError).addClass('not-null');
            if (!element.attr('aria-describedby')) element.parent().find('.warning-box')
                .css('display', 'block')
                .find('.warning-txt').text(invalidTxt);
            return false;
        }
        return true;
    }
    evtCheckRadio = function(groupRadio) {
        let selectedValue = false;
        for (const rb of groupRadio) {
            if (rb.checked) {
                selectedValue = rb.value;
                groupRadio.parent().parent().parent().find('.warning-box')
                    .css('display', 'none')
                break;
            } else {
                groupRadio.parent().parent().parent().find('.warning-box')
                    .css('display', 'block')
                    .find('.warning-txt').text(invalidTxt);
            }
        }
        return selectedValue
    }

    recentryForm
        .on('blur change', '#first_name, #last_name, #rec_sei, #rec_mei, #year_of_birth, #month_of_birth, #day_of_birth, #age, #address, #experience', function(evt) {
            validItems[evt.target.getAttribute('id')] = evtValidInput(
                $(this), (this.value || '').trim(), isValidName
            );
        })
        .on('blur change', '#rec_email', function(evt) {
            validItems[evt.target.getAttribute('id')] = evtValidInputEmail(
                $(this), (this.value || '').trim(), isValidName
            );
        })
        .on('blur change', '#rec_phone', function(evt) {
            validItems[evt.target.getAttribute('id')] = evtValidInputPhone(
                $(this), (this.value || '').trim(), isValidName
            );
        })
        .on('blur change', '#zipcode', function(evt) {
            validItems[evt.target.getAttribute('id')] = evtInputZipCode(
                $(this), (this.value || '').trim(), isValidName
            );
        })
        .on('blur keyup', '#experience', function(evt) {
            var characterCount = $(this).val().length,
                current = $('#current'),
                maximum = $('#maximum'),
                theCount = $('#the-count');
            current.text(characterCount);
            if ($(this).val().length >= 2000) {
                current.css('color', '#ed1b24');
                maximum.css('color', '#ed1b24');
                theCount.css('font-weight', 'bold');
            } else {
                current.css('color', '#666');
                maximum.css('color', '#666');
                theCount.css('font-weight', 'normal');
            }
        })


    .find('input.require_input').parent().find('.warning-box').css('display', 'none').find('.warning-txt').text(invalidTxt);

    var validateFormData = function() {
        var NumberPhone = $('#rec_phone'),
            Email = $('#rec_email'),
            Job = $('#rec_job'),
            Sex = $('input[name="sex_rec"]'),
            Province = $('#province'),
            ZipCode = $('#zipcode'),

            isValidInput = true;

        $('.input-plain').each((idx, ele) => {
            if (!evtValidInput($(ele), $(ele).val()))
                isValidInput = false;
        });

        isValidInput = evtValidInputPhone(NumberPhone, NumberPhone.val()) && isValidInput;
        isValidInput = evtValidInputEmail(Email, Email.val()) && isValidInput;
        isValidInput = evtValidSelectOption(Job, Job.val()) && isValidInput;
        isValidInput = evtCheckRadio(Sex) && isValidInput;
        isValidInput = evtValidSelectOption(Province, Province.val()) && isValidInput;
        isValidInput = evtInputZipCode(ZipCode, ZipCode.val()) && isValidInput;

        return isValidInput;
    }

    $('#next-step').on('click', function(evt) {
        if (!validateFormData()) return false;


        $('#rec-entry__form').submit();




    });

    $('.inquiries__input').each(function() {
        $(this).on('focus', function(event) {
            $(this).removeClass('not-null').parent().find('.warning-box').css('display', 'none');
        });
    });
})(jQuery);