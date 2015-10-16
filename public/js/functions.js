/**
 * retrieve data by username
 * @param string username
 */
function getTweets(username)
{
    //disable submit button
    var button = $('#tw_search_form button');
    button.attr('disabled','disabled').html('grabbing tweets');

    //perform ajax request
    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {'q': username },
        url: 'api/tweets.php',
        error: function (jqXHR, textStatus, errorThrown) {
            //show and highlight error msg
            $('#msg').html(errorThrown+ ': '+jqXHR.responseText)
                .attr('class', 'text-danger');

            button.attr('disabled',null).html('Get them!');
        },
        success: function (data) {
            var values = [];
            //transform tweets per hour into x,y graph data
            $.each(data.hours, function(hour, value){
                values.push({
                    y: value,
                    x: hour+':00'
                });
            });

            var graphData = [{
                key: 'Tweets',
                values: values
            }];

            renderGraph('#graph svg',graphData);

            var msg = data.count+' tweets of max 500 found for this user.';

            if(data.time_zone.length > 0){
                msg += '<br>Hours according to users time zone: '+data.time_zone;
            }

            $('#msg').html(msg)
                .attr('class', 'text-success');

            button.attr('disabled',null).html('Get them!');
        }
    });
}

/**
 * render a nvd3 bar chart graph
 * @param string elemSelector
 * @param array data
 */
function renderGraph(elemSelector, data)
{
    //add bar chart
    nv.addGraph({
        generate: function() {
            var chart = nv.models.multiBarChart();
            chart.yAxis.tickFormat(d3.format(',d'));
            chart.xAxis.axisLabel('Hour of the day');
            chart.showControls(false);

            var svg = d3.select(elemSelector)
                .datum(data);

            svg.transition().duration(0).call(chart);
            return chart;
        },
        //callback to recalc the width of the graph on window resize (height is fixed)
        callback: function(graph) {
            nv.utils.windowResize(function() {
                var width = $(elemSelector).parent().width();
                //var height = nv.utils.windowSize().height;
                graph.width(width);//.height(height);
                d3.select(elemSelector)
                    .attr('width', width)
                    //      .attr('height', height)
                    .transition().duration(0)
                    .call(graph);
            });
        }
    });
}