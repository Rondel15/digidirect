define(['ko'], function (ko) {
    return {
        code: ko.observable(false),
        amount: ko.observable(false),
        isValid: ko.observable(false),
        isChecked: ko.observable(false)
    };
});
