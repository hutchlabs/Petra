<br><br><h1 id="closingprice" class="fltLeft">Fund Closing Prices</h1><br/><br/>

<div class="widget">
	<div class="widget-header">
		<i class="icon-signal"></i>
		<h3>Fund Closing Price</h3>
		<ul style="float: right" class="nav nav-tabs">
					<li class="active">
					  <a href="#fc-histgraph-<?php echo preg_replace('/ /','',$tier) ?>" data-toggle="tab">Graph View</a>
					</li>
					<li>
					  <a href="#fc-histtable-<?php echo preg_replace('/ /','',$tier) ?>" data-toggle="tab">Table View</a>
					</li>
		</ul>
	</div> 

	<div class="widget-content">
		<div class="pchart" id="employer-area-chart-<?php echo preg_replace('/ /','',$tier) ?>" data="<?php echo $clist ?>" rel="<?php echo $tier ?>">
		</div>

			<div class="tab-content">
  				<div class="tab-pane active" id="fc-histgraph-<?php echo preg_replace('/ /','',$tier) ?>">	
					<div id="fcp-<?php echo preg_replace('/ /','',$tier) ?>" style="height: 500px; min-width: 500px"></div>
				</div>
  				<div class="tab-pane" id="fc-histtable-<?php echo preg_replace('/ /','',$tier) ?>">	
					<div id="fcp-<?php echo preg_replace('/ /','',$tier) ?>-t"></div>

			</div>
		</div>		
	</div>
</div>
