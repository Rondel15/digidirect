const mixin = {
    initialize() {
        this._super();
        this.digidirectPopupCustom();

        return this;
    },
    digidirectPopupCustom: function () {
        if ((typeof this.popUpForm === 'function' || typeof this.popUpForm === 'object' && !!this.popUpForm)
            &&
            (typeof this.popUpForm.options === 'function' || typeof this.popUpForm.options === 'object' && !!this.popUpForm.options)
        ) {
            this.popUpForm.options.modalClass = ' -content-scroll';
        }

        return this;
    }
};

export default target => target.extend(mixin);