<h1 style="font-size:small">[<a id="tbl_contrib-<?php echo $tier.'-'.$mid ?>" class="e2e">Export</a>]</h1>

<table id="e2e-tbl_contrib-<?php echo $tier.'-'.$mid ?>" class="table table_contrib table-striped table-bordered tablesorter paginated">
				<thead>
					<tr>
						<th>Date</th>
						<th>Scheme</th>
						<th>Employee</th>
						<th>Employer</th>
						<th>Redemption</th>
						<th>Total</th>
					</tr>
				</thead>
				<tbody>
        
                <?php foreach($dealinfo as $d => $in) { 
                        foreach($in as $i){ 
							  if ($i['tier']=='Tier '.$tier) {
				?>
				<tr>
					<td><?php echo date_format(date_create($d),'jS F Y') ?></td>
					<td><?php echo $i['scheme'] ?></td>
					<td><?php echo number_format($i['employee'],2) ?></td>
					<td><?php echo number_format($i['employer'],2) ?></td>
					<td><?php echo number_format($i['redemption'],2) ?></td>
					<td><?php echo number_format($i['total'],2) ?></td>
				</tr>
				<?php }}} ?> 
				</tbody>
			</table>
