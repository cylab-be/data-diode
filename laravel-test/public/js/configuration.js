$(() => {
    $("#ports-table").tabulator({
        layout:"fitColumns",
        columns:[
            {title:"ID", field:"id", visible: false},
            {title:"Input Port", field:"input_port"},
            {title:"Ouput Port", field:"output_port"}
        ],
        ajaxURL:"/rule",
        ajaxResponse: (url, params, response) => {
            return response.data;
        }
    });
});