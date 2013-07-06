<div id="myContribModal-<?php echo $m->id?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3 id="myModalLabel"><?php echo  ucwords(strtolower($m->name))?>'s Contributions</h3>
  </div>

  <div class="modal-body">
		<ul class="nav nav-tabs">
		<?php $c='active';
			  for($i=2; $i<5; $i++) { 
			  	$k = ($i==4) ? 'Post Tax' : 'Tier '.$i;	

                if (in_array($m->id,array_keys($tierinfo))) {
                   if(in_array('Tier '.$i, $tierinfo[$m->id])) { 
				  	$id = '#mtier'.$i.'-'.$m->id;
		?>
		<li class="<?php echo $c?>"><a href="<?php echo $id?>" data-toggle="tab"><?php echo $k ?> Funds</a></li>
	   <?php $c='';}} else { ?>
<li>
   <a href="javacript:;" title="You have no <?php echo $k?> Funds" style="color:#aaa"><?php echo $k?> Funds</a>
</li>
		<?php }} ?>

		</ul>

		<div class="tab-content">
  
			<!-- Tier 2 tab -->
			<?php $c='active'; if (in_array($m->id,array_keys($tierinfo))) {if(in_array('Tier 2',$tierinfo[$m->id])) { ?>
			<div class="tab-pane <?php echo $c?>" id="mtier2-<?php echo $m->id?>">
				<div>
			<?php 
				echo View::factory('employer/_employee_contributions')
								->set('dealinfo', $dealinfo) 
								->set('mid', $m->id) 
								->set('tier', 2); 
                    $c = '';
			?>
				</div>
			</div>
			<?php }} ?>

			<!-- Tier 3 tab -->
			<?php if(in_array($m->id,array_keys($tierinfo))) { if(in_array('Tier 3',$tierinfo[$m->id])) { ?>
			<div class="tab-pane <?php echo $c?>" id="mtier3-<?php echo $m->id?>">
				<div>
			<?php 
				echo View::factory('employer/_employee_contributions')
								->set('dealinfo', $dealinfo) 
								->set('mid', $m->id) 
								->set('tier', 3); 
                    $c = '';
			?>
				</div>
			</div>
			<?php }} ?>

			<!-- Tier 4 tab -->
			<?php if (in_array($m->id,array_keys($tierinfo))) {if(in_array('Tier 4',@$tierinfo[$m->id])) { ?>
			<div class="tab-pane <?php echo $c?>" id="mtier4-<?php echo $m->id?>">
				<div>
			<?php 
				echo View::factory('employer/_employee_contributions')
								->set('dealinfo', $dealinfo) 
								->set('mid', $m->id) 
                                ->set('tier', 4);
			?>
				</div>
			</div>
			<?php }} ?>
		</div>
  </div> 

  <div class="modal-footer">
    <button data-dismiss="modal" class="btn btn-primary">Close</button>
  </div>

</div>
