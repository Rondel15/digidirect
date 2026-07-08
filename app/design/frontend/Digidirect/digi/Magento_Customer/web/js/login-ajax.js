require(['jquery'],function($){

    console.log("Login AJAX Test!");

    const loadconfigdata = localStorage.getItem("configData-dbd6c84f-2332-ec11-aae9-02dca44cceec");
    const myObj = JSON.parse(loadconfigdata);
    var magento_pa_id = myObj.c;

    localStorage.setItem("Magento_PA_Id", magento_pa_id); 
    console.log("magento_pa_id: " + magento_pa_id);

    var param = magento_pa_id;
    var YOUR_URL_HERE = 'getpaidsalesforce';

    $.ajax({
        url: YOUR_URL_HERE,
        type: "POST",
        data: {pa_id: param},
        dataType: "json",
        success: function() {
            console.log("Thank you for subscribing!");
        },
        error: function() {
            console.log("There was an error. Try again please!");
        }
    });

});