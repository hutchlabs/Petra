<div class="span12">

<?php $tn = preg_replace('/ /','',$tier); ?>

<div class="widget">
	<div class="widget-header">
		<i class="icon-signal"></i>
		<h3>Fund Closing Price</h3>

		<ul style="float: right" class="nav nav-tabs">
			<li class="active">
                <a href="#fc-histgraph-<?php echo $tn ?>" 
                   data-toggle="tab">Graph View</a>
			</li>
			<li>
                 <a href="#fc-histtable-<?php echo $tn ?>" 
                    data-toggle="tab">Table View</a>
			</li>
		</ul>
	</div> 

	<div class="widget-content">
        <div id="employee-area-chart-<?php echo $tn ?>" 
             data="<?php echo $clist ?>" rel="<?php echo $tier ?>">
		</div>		
		
	   <div class="tab-content">
          <div class="tab-pane active" id="fc-histgraph-<?php echo $tn?>">
             <div id="fcp-<?php echo $tn?>-perf" class="alert alert-info" style="font-size:14px;">
                    <span>Fund Peformance: calculating...</span><br/>
              </div>

              <div id="fcp-<?php echo $tn ?>" style="height: 580px; min-width: 500px;"></div>
		  </div>
          <div class="tab-pane" id="fc-histtable-<?php echo $tn ?>">	
				<div id="fcp-<?php echo $tn ?>-t"></div>
		  </div>
    </div>
	</div>
</div>

</div>
