var colors = ["#4572A7","#AA4643","#89A54E","#80699B","#3D96AE",
			  "#DB843D","#92A8CD","#A47D7C","#B5CA92"];



$(function () {

	$('#tax-slider').slider({
		value: 25,
		min: 0,
		max: 100,
		step: 0.5,
		slide: function( event, ui ) {
                $( "#taxrate" ).html(ui.value + '%' );
				var val = $('#fvalue').val() * (1-(ui.value/100));
				val = Math.round(val*100)/100;
                if (!isFinite(val)) { val = 0;}
                $("#tier3v").html('<b>'+ val + '</b>' );
       }
	});

	$('.resetpass').click(function(){
		var empid = this.id;
		CallServer('employee_resetpass', {'id': empid}, ResetPass);
	});


	$('.e2e').click(function() {
        
    	var uri = 'data:application/vnd.ms-excel;base64,',
        	template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
        base64 = function(s) {
            return window.btoa(unescape(encodeURIComponent(s)))
        },
        format = function(s, c) {
            return s.replace(/{(\w+)}/g, function(m, p) {
                return c[p];
            })
        }
		var ctx = {
                worksheet: 'Portfolio Balance',
                table: $('#e2e-'+this.id).html()
        }
        window.location.href = uri + base64(format(template, ctx))
	});

	
	var t2_employee_id = ($("#employee-area-chart-Tier2")) 
				    ? $("#employee-area-chart-Tier2").attr('data') : null;
	var t3_employee_id = ($("#employee-area-chart-Tier3")) 
				    ? $("#employee-area-chart-Tier3").attr('data') : null;
	var t4_employee_id = ($("#employer-area-chart-Tier4")) 
				    ? $("#employee-area-chart-Tier4").attr('data') : null;

	if (t2_employee_id) { 
		var tier = $("#employee-area-chart-Tier2").attr('rel');
		CallServer('employee_fundinfo', 
				   {'id': t2_employee_id,'tier':tier,'svg':'fcp-Tier2'}, FundChart);
	}
	if (t3_employee_id) { 
		var tier = $("#employee-area-chart-Tier3").attr('rel');
		CallServer('employee_fundinfo', 
				   {'id': t3_employee_id,'tier':tier,'svg':'fcp-Tier3'}, FundChart);
	}
	if (t4_employee_id) { 
		var tier = $("#employee-area-chart-Tier4").attr('rel');
		CallServer('employee_fundinfo', 
				   {'id': t4_employee_id,'tier':tier,'svg':'fcp-Tier4'}, FundChart);
	}


	var t2_employer_id = ($("#employer-area-chart-Tier2")) 
				    ? $("#employer-area-chart-Tier2").attr('data') : null;
	var t3_employer_id = ($("#employer-area-chart-Tier3")) 
				    ? $("#employer-area-chart-Tier3").attr('data') : null;
	var t4_employer_id = ($("#employer-area-chart-Tier4")) 
				    ? $("#employer-area-chart-Tier4").attr('data') : null;

	if (t2_employer_id) { 
		var tier = $("#employer-area-chart-Tier2").attr('rel');
		CallServer('employer_fundinfo', 
				   {'id': t2_employer_id,'tier':tier,'svg':'fcp-Tier2'}, FundChart);
	}
	if (t3_employer_id) { 
		var tier = $("#employer-area-chart-Tier3").attr('rel');
		CallServer('employer_fundinfo', 
				   {'id': t3_employer_id,'tier':tier,'svg':'fcp-Tier3'}, FundChart);
	}
	if (t4_employer_id) { 
		var tier = $("#employer-area-chart-Tier4").attr('rel');
		CallServer('employer_fundinfo', 
				   {'id': t4_employer_id,'tier':tier,'svg':'fcp-Tier4'}, FundChart);
	}

	var balance_id = ($("#balancehistory-area-chart")) 
				    ? $("#balancehistory-area-chart").attr('data') : null;

	if (balance_id) { 
		var ti2 = ($('#hbh2').attr('id')=='hbh2') ? 1 : 0;
		var ti3 = ($('#hbh3').attr('id')=='hbh3') ? 1 : 0;
		var ti4 = ($('#hbh4').attr('id')=='hbh4') ? 1 : 0;
		CallServer('balance_history', 
				   {'id': balance_id,'tier2':ti2, 'tier3':ti3,'tier4':ti4},
				   BalanceHistory);
	}
});

function ResetPass(data,params)
{
	var success = (data=='done') ? true : false;
	var aclass = (success) ? 'alert alert-success' : 'alert alert-error';
	var msg = (success) ? 'Employee account has been successfully reset.' : 'Error: could not reset user account - user has not created an account yet.';
	var notice = '#notice-'+params.id;
	if ($(notice)) {
		$(notice).addClass(aclass).html(msg)
	}
    document.location.reload(true);
}

function FundPerformance(data, min, max, id)
{
  var minDate = new Date(); minDate.setTime(min);
  
  var maxDate = new Date(); maxDate.setTime(max);

  var html = 'Fund Performance from <b>'+
             minDate.toUTCString().replace(/\d+:\d+:\d+ GMT/,'')
             +'</b> to <b>'+
             maxDate.toUTCString().replace(/\d+:\d+:\d+ GMT/,'')+
             '</b><br/><br/>';   

  min = min.toString().replace(/\.\d+$/,'');
  max = max.toString().replace(/\.\d+$/,'');

  for(k in data)
  {
      var pMin = 0, pMax= 0;
      pMin = findClosestValue(data[k].data, min);
      pMax = findClosestValue(data[k].data, max);

      var p = ((pMax/pMin) - 1);
      p = p * 100;
      p = Math.round(p*100)/100;
      if (!isFinite(p)) { p = 0;}
      html += data[k].name+': <b>'+p+'</b>%';
      html += '<br/><br/>';
  }
  $(id).html(html);
}

function findClosestValue(data, time)
{
  var val = 0;

  for(j in data)
  {
     if (data[j][0] == time) { return data[j][1]; }
     if (data[j][0] > time) { return val; }
     val = data[j][1];     
  }
}

function BalanceHistory(data, a)
{
	var d = Array(), idx = 0;

	if (a['tier2']) { d[idx] = {name:'Tier 2',data:data.Tier2};   idx++; }
	if (a['tier3']) { d[idx] = {name:'Tier 3',data:data.Tier3};   idx++; }
	if (a['tier4']) { d[idx] = {name:'Post Tax',data:data.Tier4}; idx++; }

	BuildMultiLineChart('hbh', 'Balance History', 'Balance (GHC)', d);
	BuildBalanceHistoryTable('hbht', data.dates, d);
}


function BuildBalanceHistoryTable(container, categories, data) 
{
	var header = '<h1 style="font-size:medium">Balance History '+
    			 '[<a class="e2e" id="'+container+'">Export</a>]'+
				 '</h1><br/>';

	var style = 'table table-striped table-bordered tablesorter';
	var table = '<table id="e2e-'+container+'" class="'+style+'">';
	table = header + table;

	table += '<thead><tr><th style="width: 20%">Date</th>'
	for(k in data) 
	{
		table+='<th style="text-align:center">'+data[k].name+'</th>';
	}	
    table += '</tr></thead><tbody>';

	for(var i= categories.length-1; i >= 0; i--) {
		table +='<tr><td>'+categories[i]+'</td>';
		for(k in data) 
		{
            p = (data[k].data[i][1]).formatMoney(2,'.',',');
			table += '<td style="text-align:center"> GHC '+p+'</td>';
		}	
	    table += '</tr>'; 
	}
	table += '</tbody></table>';

	$('#'+container).html(table);

	$('.e2e').click(function() {
    	var uri = 'data:application/vnd.ms-excel;base64,',
        	template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
        	base64 = function(s) {
            	return window.btoa(unescape(encodeURIComponent(s)))
        	},
        	format = function(s, c) {
            	return s.replace(/{(\w+)}/g, function(m, p) {
                	return c[p];
            	})
        	}
			var ctx = {
                worksheet: 'Portfolio Balance History',
                table: $('#e2e-'+this.id).html()
        	}
        window.location.href = uri + base64(format(template, ctx))
	});
}

function FundChart(data, a)
{
	if (data.categories.length == 0)
	{
		$(a.svg).html('No data available');
	}
	else 
	{
		BuildMultiLineChart(a['svg'], 'Price History', 'Fund Price (GHC)', data.data);
	    BuildFCTable(a['tier'],a['svg']+'-t',data.categories, data.data);
	}
}

function BuildFCTable(title, id, categories, data)
{
	var alink = '[<a class="e2e" id="'+id+'">Export</a>]';

    title = (title=='Tier 4') ? 'Post Tax' : title;
	var table = '<h1 style="font-size:medium">'+title+' Fund Closing Price'+alink+'</h1><br/><table id="e2e-'+id+'" class="table table-striped table-bordered tablesorter bpaginated"><tr><th>Date</th><th>Scheme</th><th>Price</th></tr><tbody>';

	for(k in data)
	{
	    for(var j= data[k].data.length-1; j >= 0; j--) 
		{
			var dt = new Date(data[k].data[j][0]);
			var p = data[k].data[j][1];
            p = p.formatMoney(2,'.',',');
			table +='<tr><td>'+fdate(dt)+'</td>'+
					'<td>'+data[k].name+'</td>'+
					'<td style="text-align:right"> GHC '+p+'</td>'+
					'</tr>'; 
		}
	}
	table += '</tbody></table>';

	$('#'+id).html(table);

	$('.e2e').click(function() {
    	var uri = 'data:application/vnd.ms-excel;base64,',
        	template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
        base64 = function(s) {
            return window.btoa(unescape(encodeURIComponent(s)))
        },
        format = function(s, c) {
            return s.replace(/{(\w+)}/g, function(m, p) {
                return c[p];
            })
        }
		var ctx = {
                worksheet: 'Fund Closing Price',
                table: $('#e2e-'+this.id).html()
        }
        window.location.href = uri + base64(format(template, ctx))
	});
}



function BuildMultiLineChart(container, title, ytitle, data)
{
 	new Highcharts.StockChart({
         		chart: { 
                   renderTo: container, 
                   width: 850,
                   events: {
                        redraw: function(event) {
                                var x = event.currentTarget.xAxis
                                var id = '#'+container+'-perf';
                                FundPerformance(data,x[0].min,x[0].max,id);
                        }
                    },  
                },
         		title: { text: title },
		 		rangeSelector: { selected: 4,
                                 enabled: true,
                                 inputEnabled: ((container=='hbh') ? true:false),
                },
         		yAxis: { 
                    title: { text: ytitle }, 
			    },
			credits: { enabled: false },
			legend: { enabled: true, y: -80 },
			tooltip: {
		    	pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
		    	valueDecimals: 2
		    },
         	series: data 
     });

    if (title=='Price History') {
        var start = data[0].data[0][0];
        var end =   data[0].data[data[0].data.length-1][0];
        FundPerformance(data,start, end, '#'+container+'-perf');
    }
}

function BuildChart(container, ctitle, ytitle, sname, sdata)
{
	return new Highcharts.StockChart({
         chart: { renderTo: container },
         title: { text: ctitle },
		 rangeSelector: { selected: 1 },
         yAxis: { title: { text: ytitle }, },
         series: [{
				name: sname,
				data: sdata,
				tooltip: { valueDecimals: 2 }
			}]
      });
}

function fdate(date)
{
    var m_names = new Array("January", "February", "March", 
        "April", "May", "June", "July", "August", "September", 
        "October", "November", "December");

    var curr_date = date.getDate();
    var sup = "";
    if (curr_date == 1 || curr_date == 21 || curr_date ==31)
    {
        sup = "st";
    }
    else if (curr_date == 2 || curr_date == 22)
    {
        sup = "nd";
    }
    else if (curr_date == 3 || curr_date == 23)
    {
        sup = "rd";
    }
    else
    {
        sup = "th";
    }

    var curr_month = date.getMonth();
    var curr_year = date.getFullYear();

    return (curr_date+sup + ' '+m_names[curr_month]+' '+ curr_year);
}
