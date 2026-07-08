import $ from 'jquery';
import View from './../index';

export default class ViewCustom extends View {
    progress () {
        $('body').loader().trigger('processStart');
    }
    success (data) {
        super.success(data);
        $('body').loader().trigger('processStop');
    }
}
