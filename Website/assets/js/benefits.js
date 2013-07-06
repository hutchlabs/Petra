Number.prototype.mod = function(n) {
return ((this%n)+n)%n;
}

$(function(){

// declare vars in global scope
var c5; // Current Age
var c6; // Retirement Age
var c7; // Years to Retirement
var c8; // Current Salary
var c9; // Portion of Salary Invested
var c10; // Annual Contribution
var c11; // Projected Average Annual Salary Growth
var c12; // Projected Average Investment Returns
var c14; // Lump Sum

		

	// calculate years to retirement, ( C7 = C6-C5)
	$("#rage, #cage",$('#form1')).blur(function(){
		
		 c5 = $('#cage').val();
		 c6 = $('#rage').val();	 
		 if ( (c5 != "") && (c6 != "") ){
			 c7 = (parseInt(c6) - parseInt(c5)) + "";
			 $('#yretire').val( c7 );
		 }
	});	


/************** sliders range of values *********/
// salary invested slider
 $( "#psal_slider" ).slider({
	range: "min",
	value: 6,
	min: 5,
	max: 21.5,
	step: 0.5,
	slide: function( event, ui ) {
		$( "#psal" ).val(ui.value +  "%");
		// calculate annual growth based on salary invested slider
		c9 = $('#psal').val(); //  used later for drawing chart
		
		// calculate Annual contribution, ( C10 = C8*C9 )			
		c8 = $('#csal').val();	
		c10 = (parseFloat(c8) * (parseFloat(c9) * 0.01) ) + "";
		$('#acon').val( c10 );
	}
});
$( "#psal" ).val( $( "#psal_slider" ).slider( "value" ) +  "%");

// annual salary growth slider
	$( "#pgro_slider" ).slider({
	range: "min",
	value: 6,
	min: 0,
	max: 50,
	slide: function( event, ui ) {
		$( "#pgro" ).val( ui.value +  "%");
	}
});
$( "#pgro" ).val($( "#pgro_slider" ).slider( "value" ) +  "%");

//  investment returns slider
	$( "#pret_slider" ).slider({
	range: "min",
	value: 10,
	min: 0,
	max: 30,
	slide: function( event, ui ) {
		$( "#pret" ).val( ui.value + "%");
	}
});
$( "#pret" ).val($( "#pret_slider" ).slider( "value" ) + "%" );
		
/********** end sliders ************/
// calculate Annual contribution, ( C10 = C8*C9 )
	$("#csal",$('#form1')).blur(function(){
		
		 c8 = $('#csal').val();	
		 c9 = $('#psal').val();	 
		 if ( (c8 != "") || (c8 != "") ){
			 c10 = (parseFloat(c8) *  (parseFloat(c9) * 0.01 ) ) + "";
			 var cten = accounting.formatMoney(c10, "", 0, ",", ".");
			 $('#acon').val( cten ); // 83 * 6 *0.01
		 }
	});	


// calculate Lump sum  = IF(C11=C12,(C10*12)*C7*((1+C12)^(C7-1)),(C10*12)*((1+C12)^C7-(1+C11)^C7)/(C12-C11))
	$("#btn",$('#form1')).click(function(){
		// validations
	if(c5 == "" || c6 == "" || c8 == "" ){
		alert('Error: Please fill out all form fields');
		return false;
	}else {
		
		$("#results2").show();	
		$("#results").show();		
		c11 = parseFloat( parseFloat($('#pgro').val() ) * 0.01 );
		c12 = parseFloat( parseFloat( $('#pret').val() ) * 0.01 );		
		
		
		//alert(c12 + ': '+ c10 + ': '+ c11 + ': '+ c7 ); // 0.1: 4.9799999999999995: 0.1: 45
		// calculate Lump sum  = IF(C11=C12,(C10*12)*C7*((1+C12)^(C7-1)),(C10*12)*((1+C12)^C7-(1+C11)^C7)/(C12-C11))
		c14 =  cal_lumpsum();
		if ( (c14 != "") ){
			 var mny = accounting.formatMoney(c14, "", 0, ",", ".");
			 $("#lumpsum").val(mny);			 
			 var i = parseInt(c9); 
			 var y; 
			 var lumpsum; 
			 var csum;
			 var xseries = [];
			 var yseries = [];

			 for (y=i; y < 26; y++){
				 lumpsum =  cal_lumpsum(y);
				 csum = accounting.formatMoney(lumpsum, "", 0, "", ".");
				 xseries.push( parseInt(csum) );
				 yseries.push(y);
			}
		 }
		 
		 var chart;
		 
		 // draw chart		 
		 var options = {
      chart: {
         renderTo: 'chartContainer',
		 backgroundColor: null,	
		 width: 430,				
		 height: 310,
		 plotBackgroundImage: 'http://www.petratrust.com/wp-content/themes/petra/images/bg_chart_logo.jpg',
         defaultSeriesType: 'line'
      },
      title: {
         text: 'Growth of Lump'
      },
     xAxis: {
		 gridLineDashStyle : 'ShortDash',
		 gridLineColor : '#eaeaea',
		 categories: [/*"0","5","10","15","20","25"*/],

			labels: {
				align: 'center',			
				style: {
					
					color: '#323232',
					fontFamily:'Arial',
					fontSize:'10px'								
				},
				formatter: function() {
					if(this.value.mod(5) == 0) {
						return this.value +'%';
					}else {
						return '';
					}
               
            }
			},	
         title: {
            enabled: true,
            text: 'Portion of Salary Invested'
         },
         
         maxPadding: 0.05,
         showLastLabel: true
      },
      yAxis: {
		  gridLineDashStyle : 'ShortDash',
		  gridLineColor : '#eaeaea',
		  maxPadding: 0.1,
         title: {
						margin: 50,
						text: 'Lump Sum (Ghana Cedis)',
				        style: {
				            color: '#323232',
				            fontFamily:'Arial',
				            fontSize:'10px',
				            fontWeight:'normal'
				        }	
					},
         labels: {
            formatter: function() {
               return (this.value/1000) + 'k';
            }
         },
		 showLastLabel: true,
         lineWidth: 2
      },
      legend: {	enabled: false	
	},
	  credits: {
		enabled: false
	  },
	  tooltip: {
         formatter: function() {
			 var frt = accounting.formatMoney(this.y, "GHC ", 0, ",", ".");
                   return 'Lump Sum: '+
               this.x +'%: '+  frt;
         }
	  },
      series: []
	  
		 } // end for option var
		 
		 var series = {
                data: []
            };
			$.each(yseries, function(index, value) {                
						options.xAxis.categories.push(value);
            });
            $.each(xseries, function(index, value) {   
					//series.name = index;          
					series.data.push(parseFloat(value));
            });
            
            options.series.push(series);

		 
  	chart = new Highcharts.Chart(options);
	
	}
	});	
	
// calculate Lump sum  = IF(C11=C12,(C10*12)*C7*((1+C12)^(C7-1)),(C10*12)*((1+C12)^C7-(1+C11)^C7)/(C12-C11))
function cal_lumpsum(cnine){
	// c vars are global
	c8 = $('#csal').val();	
	c9 = $('#psal').val();
	if (typeof(cnine) !=='undefined') {
		 c9 = cnine; // set for dynamic range based calculation for chart
	}
	c10 = (parseFloat(c8) *  (parseFloat(c9) * 0.01 ) ) + "";
	//var cten = accounting.formatMoney(c10, "", 0, ",", ".");
	
	if( c11 == c12 ) {
			// (C10*12)*C7*((1+C12)^(C7-1))
			c14 =  	parseFloat(	 (c10 * 12) * c7 * ( Math.pow( (1+c12) , (c7-1) )   ) );
		} else {
			// (C10*12)*((1+C12)^C7-(1+C11)^C7)/(C12-C11)
			c14 =  	parseFloat(	 (c10 * 12) *  ( ( ( Math.pow( (1+c12) , c7 ) ) - ( Math.pow( (1+c11) , c7 ) ) ) / (c12-c11) ) );
		}
		return (c14 != "") ? c14 : false;
}

});

