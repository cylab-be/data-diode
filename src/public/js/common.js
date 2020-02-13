$(function() {
    $("#ports-table").tabulator({
        layout:"fitColumns",
        columns: columns,
        ajaxURL: "/rule",
        ajaxResponse: function(url, params, response){
            return response.data;
        },
        addRowPos: "bottom",
        cellEdited: editRule
    });
    $("#add-rule").click(function() {
        swal.resetDefaults();
        swal.setDefaults({
            progressSteps: addRuleSteps
        });
        swal.queue(addRuleQueue).then(function(result){
            addRule(result);
        });
    });
    $.validator.addMethod('ip', function(value) {
        return value.match("^(?!0)(?!.*\\.$)((1?\\d?\\d|25[0-5]|2[0-4]\\d)(\\.|$)){4}$");
    }, 'Invalid IP address');
    $.validator.addMethod('netmask', function(value) {
        return value.match("^(((128|192|224|240|248|252|254)\\.0\\.0\\.0)|(255\\.(0|128|192|224|240|248|252|254)\\.0\\.0)|(255\\.255\\.(0|128|192|224|240|248|252|254)\\.0)|(255\\.255\\.255\\.(0|128|192|224|240|248|252|254)))$");
    }, 'Invalid subnet mask');
    $("#network-configuration").validate({
        errorElement: "span",
        errorClass: "help-block",
        wrapper: "strong",
        success: function(label, element){
            if($(element).is(":radio")) {
                $(element).parent().parent().parent().parent().removeClass("has-error");
            } else {
                $(element).parent().parent().removeClass("has-error");
            }
        },
        showErrors: function(errorMap, errorList) {
            errorList.forEach(function(error) {
                if($(error.element).is(":radio")){
                    $(error.element).parent().parent().parent().parent().addClass("has-error");
                } else {
                    $(error.element).parent().parent().addClass("has-error");
                }
            });
            this.defaultShowErrors();
        },
        rules: {
            mode: {
                required: true
            },
            ip: {
                required: isFieldRequired,
                ip: isFieldRequired
            },
            netmask: {
                required: isFieldRequired,
                netmask: isFieldRequired
            }
        },
        errorPlacement: function(error, element) {
            if ( element.is(":radio") ) {
                error.appendTo(element.parent().parent().parent());
            } else {
                error.insertAfter( element );
            }
        },
        submitHandler: function(form) {
            $("#save-network").prop("disabled", true);
            $.ajax({
                type: "PUT",
                url: "/network/update",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: networkValues(),
                error: function (xhr, status, error) {
                    $("#save-network").prop("disabled", false);
                    displayError(xhr, status, error);
                },
                success: function() {
                    $("#save-network").prop("disabled", false);
                    toastr.success("Successfully updated !");
                }
            });
        }
    });
    $.ajax({
        type: "GET",
        url: "/network",
        success: function(data) {
            $("#" + data.mode).prop("checked", true);
            $("#ip").val(data.address);
            $("#netmask").val(data.netmask);
        }
    })
});

function isFieldRequired(event) {
    return $("#static").prop("checked");
}

function networkValues(){
    if($("#dhcp").prop("checked")) {
        return {
            mode: "dhcp"
        };
    } else {
        return {
            mode: "static",
            ip: $("#ip").val(),
            netmask: $("#netmask").val()
        };
    }
}

function deleteRow(row){
    $.ajax({
        type: "DELETE",
        url: "/rule/" + row.row.data.id,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        error: displayError,
        success: function() {
            $("#ports-table").tabulator("deleteRow", row);
            toastr.success("Successfully deleted !");
        }
    });
}

function validatePort(value){
    return new Promise(function(resolve, reject){
        if(!value){
            reject("Port number is needed");
        } else {
            var intValue = parseInt(value);
            if(intValue < 1 || intValue > 65535){
                reject("Port number should be between 1 and 65535");
            } else {
                resolve();
            }
        }
    });
}

function addRule(values){
    $.ajax({
        type: "POST",
        url: "/rule",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: addRuleData(values),
        error: displayError,
        success: function(data) {
            $("#ports-table").tabulator("addRow", Object.assign({id: data.id}, addRuleData(values)));
            toastr.success("Successfully added !");
        }
    });
}

function editRule(cell){
    $.ajax({
        type: "PUT",
        url: "/rule/" + cell.getRow().row.data.id,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: editRuleData(cell),
        error: displayError,
        success: function(){
            toastr.success("Successfully edited !");
        }
    });
}

function displayError(xhr, status, error) {
    toastr.error(JSON.parse(xhr.responseText).message);
}