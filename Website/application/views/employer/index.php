<h1 class="fltLeft"><?php echo $client->name?></h1>

<br/><br/>
				
<div class="main">
  <div class="main-inner">
    <div class="container">
	<div class="row">

    <ul class="nav nav-tabs">
        <?php echo View::factory('employer/_tabs')->set('tiers', $tiers); ?> 
    </ul>

    <div class="tab-content">
  
        <!-- Home tab -->
        <div class="tab-pane active" id="home">
            <?php 
                   echo View::factory('employer/_balance')
                             ->set('balance', $currentbal); 

                    echo View::factory('employer/_balance_history')
                             ->set('tiers', $tiers) 
                             ->set('clist', $client->id); 
            ?> 
        </div>

        <!-- Tabs for Tiers 2-4 tab -->
        <?php for($i=2; $i < 5; $i++) { if($tiers['Tier '.$i]) { ?>

        <div class="tab-pane" id="tier<?php echo $i?>">
	        <div class="span12">	
		    <?php 
			    echo View::factory('employer/_contributions')
					    ->set('contribs', $contribs) 
					    ->set('clist', $client->id) 
					    ->set('tier', 'Tier '.$i); 
		    ?>
	        </div>
	        <div class="span12">	
                <?php 
			    echo View::factory('employer/_fundclosing')
					    ->set('clist', $client->id) 
					    ->set('tier', 'Tier '.$i); 
                ?>
	        </div>
        </div>

        <?php }}  ?>


        <div class="tab-pane" id="employees">
		
<div class="span12">
	<div class="widget widget-table action-table">
		<div class="widget-header">
			<i class="icon-th-list"></i>
			<h3 id="emplist">Employees</h3>
			<!--<h3 style="float:right">[<a id="emptbl" class="e2e" style="font-size:small">Export</a>]</h3>-->
		</div> 
					
		<div class="widget-content">
			<table id="e2e-emptbl" class="table table-striped table-bordered paginated" data="100">
				<thead>
					<tr>
						<th>Name</th>
						<th>Type</th>
						<th>Contributions</th>
						<th class="td-actions" style="text-align:left">Online Status</th>
					</tr>
				</thead>
				<tbody>
                    <?php 
                        foreach ($employees as $m) { 
                            $q = 'eid='.$m->id
                    ?>
					<tr>
						<td><?php echo  ucwords(strtolower($m->name)) ?></td>
						<td><?php echo $m->description ?></td>
						<td>
                            <a target="_blank" href="/members/employer/usercontribs?cid=<?php echo $client->id ?>&eid=<?php echo $m->id?>" role="button" class="btn btn-small">Contributions</a>
						</td>
						<td>
							<?php if (in_array($m->id, $accounters)) { ?>
							<a href="#myResetModal-<?php echo $m->id?>" role="button" class="btn btn-small btn-warning" data-toggle="modal">Reset user account</a>
							<?php } else { ?>
								<span>Online account has not been setup</span>
                            <?php } ?>
						</td>
					</tr>
                    <?php } ?>
				</tbody>
			</table>
		</div> 
	</div> 
</div> 

  </div>
</div>

<!-- Modals -->
<?php 
foreach ($employees as $m) { 
    echo View::factory('employer/modal_employee_resetpass')->bind('m', $m); 
} 
?>
	</div> <!-- /row -->
	</div> <!-- /container -->
   </div> <!-- /main-inner -->
</div> <!-- /main -->

