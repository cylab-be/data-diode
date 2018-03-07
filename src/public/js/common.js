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
});

function deleteRow(row){
    $.ajax({
        type: "DELETE",
        url: "/rule/" + row.row.data.id,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        error: function() {
            toastr.error("Operation failed !");
        },
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
        error: function() {
            toastr.error("Operation failed !");
        },
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
        error: function() {
            toastr.error("Operation failed !");
        },
        success: function(){
            toastr.success("Successfully edited !");
        }
    });
}
