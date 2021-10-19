// use with <th data-sort="date">...
	$(function(){
        // Helper function to convert a string of the form "Mar 15, 1987" into a Date object.
        var date_from_string = function(str) {
          var months = ["jan","feb","mar","apr","may","jun","jul","aug","sep","oct","nov","dec"];
          var pattern = "^([a-zA-Z]{3})\\s*(\\d{1,2}),\\s*(\\d{4})$";
          var re = new RegExp(pattern);
          var DateParts = re.exec(str).slice(1);

          var Year = DateParts[2];
          var Month = $.inArray(DateParts[0].toLowerCase(), months);
          var Day = DateParts[1];

          return new Date(Year, Month, Day);
        }

        var table = $("table").stupidtable({
          "date": function(a,b) {
            // Get these into date objects for comparison.
            aDate = date_from_string(a);
            bDate = date_from_string(b);
            return aDate - bDate;
          }
        });

        table.on("beforetablesort", function (event, data) {
          // Apply a "disabled" look to the table while sorting.
          // Using addClass for "testing" as it takes slightly longer to render.
          $("#msg").text("Sorting...");
          $("table").addClass("disabled");
        });

        table.on("aftertablesort", function (event, data) {
          // Reset loading message.
          $("#msg").html("&nbsp;");
          $("table").removeClass("disabled");

          var th = $(this).find("th");
          th.find(".arrow").remove();
          var dir = $.fn.stupidtable.dir;

          var arrow = data.direction === dir.ASC ? "&uarr;" : "&darr;";
          th.eq(data.column).append('<span class="arrow">' + arrow +'</span>');
        });
    });