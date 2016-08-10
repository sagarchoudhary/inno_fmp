(function ($) {
  Drupal.behaviors.mygraph = {
    attach: function (context, settings) {
        var title  = drupalSettings.graph_report.mygraphjs.title;
        var subtit = drupalSettings.graph_report.mygraphjs.subtitle;
        var xAxis  = drupalSettings.graph_report.mygraphjs.xAxis;
        var yAxis  = drupalSettings.graph_report.mygraphjs.yAxis;
        var serie = drupalSettings.graph_report.mygraphjs.series;
        var series = [];

        $.each(serie,function(index,value){
           var person = {name:index ,data:value};
            series.push(person);
        });
      //jQuery once ensures that code does not run after an AJAX or other function that calls Drupal.attachBehaviors().
    $('#container').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: title
        },
        subtitle: {
            text: subtit
        },
        xAxis: {
            categories: xAxis,
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: yAxis
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y:.1f} lacs</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: series
    });
}
  };
})(jQuery);
