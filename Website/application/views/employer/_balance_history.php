<div class="span12">	

<div class="widget">
	<div class="widget-header">
		<i class="icon-signal"></i>
		<h3>Historical</h3>
				
		<ul style="float: right" class="nav nav-tabs">
			<li class="active">
			  <a href="#histgraph" data-toggle="tab">Graph View</a>
			</li>
			<li>
			  <a href="#histtable" data-toggle="tab">Table View</a>
			</li>
		</ul>
	</div> 

	<div class="widget-content">
        <div id="balancehistory-area-chart" 
             data="<?php echo $clist ?>"></div>		

		<div class="tab-content">
  			<div class="tab-pane active" id="histgraph">
				<?php if ($tiers['Tier 2']){ ?>
				<span id="hbh2" style="display:none"></span>
				<?php } ?>
				
				<?php if ($tiers['Tier 3']){ ?>
				<span id="hbh3" style="display:none"></span>
				<?php } ?>
				
				<?php if ($tiers['Tier 4']){ ?>
				<span id="hbh4" style="display:none"></span>
				<?php } ?>
			    <div id="hbh" style="height:580px; min-width:500px;"></div>
			</div>
			<div class="tab-pane" id="histtable">
				<div id="hbht"></div><br/><br/>
			</div>
		</div>
	</div>
</div>

</div>
