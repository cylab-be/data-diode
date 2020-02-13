var columns = [
  {title:"ID", field:"id", visible: false},
  {title:"Input Port", field:"input_port", editor: "input", validator: ["unique", "required", "integer", "min:1", "max:65535"]},
  {title:"Ouput Port", field:"output_port", editor: "input", validator: ["unique", "required", "integer", "min:1", "max:65535"]},
  {title:"Destination address", field:"destination", editor: "input", validator: ["required", "regex:^(?!0)(?!.*\\.$)((1?\\d?\\d|25[0-5]|2[0-4]\\d)(\\.|$)){4}$"]},
  {formatter: "buttonCross", align: "center", cellClick: function(e, cell){
      swal.resetDefaults();
      swal({
          title: "Warning",
          type: "warning",
          html: "Are you sure you want to delete this rule ?",
          showCloseButton: true,
          showCancelButton: true,
          focusCancel: true,
          confirmButtonText: "Confirm",
          cancelButtonText: "Cancel"
      }).then(function(){
          deleteRow(cell.getRow());
      });
  }, width: 30, headerSort: false}
];

var addRuleSteps = ["1", "2", "3"];

var addRuleQueue = [
  {
      title: "Input port",
      input: "number",
      confirmButtonText: "Next",
      showCancelButton: true,
      inputValidator: validatePort
  },
  {
      title: "Output port",
      input: "number",
      confirmButtonText: "Next",
      showCancelButton: true,
      inputValidator: validatePort
  },
  {
      title: "Destination address",
      input: "text",
      confirmButtonText: "OK",
      showCancelButton: true,
      inputValidator: validateIP
  }
];

function validateIP(value){
    return new Promise(function(resolve, reject){
        if(!value){
            reject("Destination address is needed");
        } else {
            if(!value.match("^(?!0)(?!.*\\.$)((1?\\d?\\d|25[0-5]|2[0-4]\\d)(\\.|$)){4}$")){
                reject("This is not a valid IP address");
            } else {
                resolve();
            }
        }
    });
}

function addRuleData(values) {
  return {
    input_port: values[0],
    output_port: values[1],
    destination: values[2]
  };
}

function editRuleData(cell){
  return {
      input_port: cell.getRow().row.data.input_port,
      output_port: cell.getRow().row.data.output_port,
      destination: cell.getRow().row.data.destination
  };
}

$(function(){
    $('.on').on('click', function() {
        var button = $(this)
        if(!button.hasClass('disabled')) {
            button.addClass('disabled').html('TURNING ON... <span class="fa fa-spinner fa-pulse"></span>')
            $.ajax({
                type: "POST",
                url: "/ftpserver",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    command: 'on'
                },
                error: displayError,
                success: function(response){
                    button.removeClass('disabled').html('START THE SERVER')
                    $('.state').text(response['serverState'])
                    $('.on').css('display', response['onStyle'])
                    $('.off').css('display', response['offStyle'])
                }
            })
        }
    })
})

$(function(){
    $('.off').on('click', function() {
        var button = $(this)
        if(!button.hasClass('disabled')) {
            button.addClass('disabled').html('TURNING OFF... <span class="fa fa-spinner fa-pulse"></span>')
            $.ajax({
                type: "POST",
                url: "/ftpserver",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    command: 'off'
                },
                error: displayError,
                success: function(response){
                    button.removeClass('disabled').html('STOP THE SERVER')
                    $('.state').text(response['serverState'])
                    $('.on').css('display', response['onStyle'])
                    $('.off').css('display', response['offStyle'])
                }
            })
        }
    })
})

$(function(){
    $('.on, .off').css('width', '25%')
})