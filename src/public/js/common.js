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
    $("#network-configuration").validate({
        errorElement: "span",
        errorClass: "help-block",
        wrapper: "strong",
        showErrors: function(errorMap, errorList) {
            $("#network-configuration > .has-error").each(function(index) {
                $(this).removeClass("has-error");
            });
            errorList.forEach(function(error) {
                $(error.element).parent().parent().addClass("has-error");
            });
            this.defaultShowErrors();
        },
        rules: {
            test: "required"
        },
        submitHandler: function(form) {
            console.log($(form).serialize());
        }
    });
});

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
