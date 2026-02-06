$(document).ready(function() {
  $("#transport_table").dataTable({
    language: {
      paginate: {
        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
      },
      search: "",
      searchPlaceholder: "Search..."
    },
    dom: '<"wpsp-dataTable-top"f>rt<"wpsp-dataTable-bottom"<"wpsp-length-info"li>p<"clear">>',
    order: [],
    columnDefs: [{
      targets: "nosort",
      orderable: !1
    }],
    drawCallback: function(a) {
      $(this).closest(".dataTables_wrapper").find(".dataTables_paginate").toggle(this.api().page.info().pages > 1)
    },
    responsive: !0
  }), $("#AddNew").click(function() {
    var a = new Array;
    a.push({
      name: "action",
      value: "addTransport"
    }), $.ajax({
      type: "GET",
      url: ajax_url,
      data: a,
      success: function(a) {
        $("#ViewModalContent").html(a), $(this).click()
      },
      complete: function() {
        $(".pnloader").remove(), $(this).click()
      },
      error: function() {
        $(".wpsp-popup-return-data").html("Something went wrong.."), $("#SavingModal").css("display", "none"), $("#WarningModal").css("display", "block"), $("#WarningModal").addClass("wpsp-popVisible")
      }
    })
  }), $(".EditTrans").click(function() {
    var a = $(this).attr("data-id"),
      n = new Array;
    n.push({
      name: "action",
      value: "updateTransport"
    }, {
      name: "id",
      value: a
    }), $.ajax({
      type: "GET",
      url: ajax_url,
      data: n,
      success: function(a) {
        $("#ViewModalContent").html(a)
      },
      complete: function() {
        $(".pnloader").remove(), $(this).click()
      },
      error: function() {
        $(".wpsp-popup-return-data").html("Something went wrong.."), $("#SavingModal").css("display", "none"), $("#WarningModal").css("display", "block"), $("#WarningModal").addClass("wpsp-popVisible")
      }
    })
  }), $(".ViewTrans").click(function() {
    var a = $(this).attr("data-id"),
      n = new Array;
    n.push({
      name: "action",
      value: "viewTransport"
    }, {
      name: "id",
      value: a
    }), $.ajax({
      type: "GET",
      url: ajax_url,
      data: n,
      success: function(a) {
        $("#ViewModalContent").html(a)
      },
      complete: function() {
        $(".pnloader").remove(), $(this).click()
      },
      error: function() {
        $(".wpsp-popup-return-data").html("Something went wrong.."), $("#SavingModal").css("display", "none"), $("#WarningModal").css("display", "block"), $("#WarningModal").addClass("wpsp-popVisible")
      }
    })
  }), $(document).on("click", "#TransSubmit", function(a) {
    a.preventDefault();
    deleteprocess.call(this);
    var n = $("#TransEntryForm").serializeArray();
    n.push({
      name: "action",
      value: "addTransport"
    }), $.ajax({
      type: "POST",
      url: ajax_url,
      data: n,
      beforeSend: function() {},
      success: function(a) {
        if ("success" === jQuery.trim(a)) {
          $("#SuccessModal").css("display", "block"),$("#SuccessModal .wpsp-success-text").text('Transport details saved successfully'), $("#SavingModal").css("display", "none"), $("#SuccessModal").addClass("wpsp-popVisible"), $("#TransModalBody").html(""), $("#TransModal").modal("hide");
          setTimeout(function() {
            location.reload(!0)
          }, 2000)
        } else $(".wpsp-popup-return-data").html(a), $("#TransSubmit").text('Submit'),$("#TransSubmit").attr('aria-disabled','false'),$("#TransSubmit").prop("disabled", false),$("#TransSubmit").on('click'), $("#SavingModal").css("display", "none"), $("#WarningModal").css("display", "block"), $("#WarningModal").addClass("wpsp-popVisible")
      },
      complete: function() {
        $(".pnloader").remove()
      },
      error: function() {
        $(".wpsp-popup-return-data").html("Somethng went wrong.."), $("#SavingModal").css("display", "none"), $("#WarningModal").css("display", "block"), $("#WarningModal").addClass("wpsp-popVisible")
      }
    })
  }), $(document).on("click", "#TransUpdate", function(a) {
    deleteprocess.call(this);

    if(!$("#SuccessModal").hasClass('wpsp-popVisible')){
      $("#SuccessModal").addClass('wpsp-popVisible');
    }

    a.preventDefault();
    var n = $("#TransEditForm").serializeArray();
    n.push({
      name: "action",
      value: "updateTransport"
    }), $.ajax({
      type: "POST",
      url: ajax_url,
      data: n,
      success: function(a) {
        if ("success" === jQuery.trim(a)) {
          $("#SuccessModal .wpsp-success-text").html("Transport details updated successfully."), $("#SuccessModal").css("display", "block"), $("#SavingModal").css("display", "none"), $("#SuccessModal").addClass("wpsp-popVisible"), $("#TransModalBody").html(""), $("#TransModal").modal("hide");
          setTimeout(function() {
            location.reload(!0)
          }, 1e3)
        } else $(".wpsp-popup-return-data").html(a),$("#TransSubmit").text('Submit'),$("#TransSubmit").attr('aria-disabled','false'),$("#TransSubmit").prop("disabled", false),$("#TransSubmit").on('click'), $("#SavingModal").css("display", "none"), $("#WarningModal").css("display", "block"), $("#WarningModal").addClass("wpsp-popVisible")
      },
      complete: function() {
        $(".pnloader").remove()
      },
      error: function() {
        $(".wpsp-popup-return-data").html("Somethng went wrong.."), $("#SavingModal").css("display", "none"), $("#WarningModal").css("display", "block"), $("#WarningModal").addClass("wpsp-popVisible")
      }
    })
  }), $(document).on("click", "#d_teacher", function(a) {
    var n = $(this).data("id");
    console.log(n), $("#teacherid").val(n), $("#DeleteModal").css("display", "block")
  }), $(document).on("click", ".ClassDeleteBt", function() {

    deleteprocess.call(this);

    if(!$("#SuccessModal").hasClass('wpsp-popVisible')){
    $("#SuccessModal").addClass('wpsp-popVisible');
}


    var nn = $('#wps_generate_nonce').val();
    var a = $("#teacherid").val(),
      n = new Array;
    n.push({
      name: "action",
      value: "deleteTransport"
    }, {
      name: "id",
      value: a
    },{
      name: "wps_generate_nonce",
      value: nn
    }), $.ajax({
      type: "POST",
      url: ajax_url,
      data: n,
      success: function(a) {
        if ("success" === jQuery.trim(a)) {
          $("#SuccessModal").css("display", "block"), $("#DeleteModal").css("display", "none"), $("#SuccessModal .wpsp-success-text").text('Vehicle Deleted Successfully'),
          setTimeout(function() {
	          location.reload();
          }, 2000);
        }else{
          $(".wpsp-popup-return-data").html("Somethng went wrong.."), $("#SavingModal").css("display", "none"), $("#WarningModal").css("display", "block"), $("#WarningModal").addClass("wpsp-popVisible");
        }
      },complete: function() {
        $(".pnloader").remove()
      },
      error: function() {
        $(".wpsp-popup-return-data").html("Somethng went wrong.."), $("#SavingModal").css("display", "none"), $("#WarningModal").css("display", "block"), $("#WarningModal").addClass("wpsp-popVisible")
      }
    })
  })
});
