	<br><br>
	<h1 id="contributions" class="fltLeft">Contributions</h1>
	<br><br>

	<div class="widget">
		<div class="widget-header">
			<i class="icon-signal"></i>
			<h3>Historical Contributions</h3>
			<h3 style="float:right">[<a id="tbl_contrib" class="e2e" style="font-size:small">Export</a>]</h3>
		</div> 

		<div class="widget-content">
			<table id="e2e-tbl_contrib" class="table table-striped table-bordered tablesorter paginated">
				<thead>
					<tr>
						<th>Date</th>
						<th>Scheme</th>
						<th>Employee</th>
						<th>Employer</th>
						<th>Redemption</th>
						<th>Total</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>
				<?php 
                    foreach($contribs as $date => $arr1) { 
						foreach($arr1 as $t => $arr2) { 
                             $s = ''; 
                             $qlist = implode(',',array_keys($arr2)); 
                             $worker = 0; $boss = 0; $redem = 0; $total=0;
							 foreach($arr2 as $fid => $i) { 
								if ($t==$tier) {
									$s = $i['name']; 
									$boss += $i['employer']; 
									$worker += $i['employee']; 
									$total += $i['total']; 
									$redem += $i['redemption']; 
                                }
                            }
						    if ($t==$tier) {
                                $q = 'date='.$date.'&scheme='.$qlist.'&'.
                                     'tier='.$t.'&cid='.$clist;
				?>
				<tr>
					<td><?php echo date_format(date_create($date),'jS F Y') ?></td>
					<td><?php echo $s ?></td>
					<td><?php echo number_format($worker,2) ?></td>
					<td><?php echo number_format($boss,2) ?></td>
					<td><?php echo number_format($redem,2) ?></td>
					<td><?php echo number_format($total,2) ?></td>
					<td>
                    <a target="_blank" href="/members/employer/contribdetails?<?php echo $q?>" role="button" class="btn btn-small">Details</a>

					</td>
				</tr>
				<?php }}} ?> 
				</tbody>
			</table>
		</div> 
	</div> 
