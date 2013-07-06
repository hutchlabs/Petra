<div id="<?php echo $idx?>" tabindex="-1">

<div>
    <h3 id="myModalLabel">Contributions for <?php echo date_format(date_create($date),'jS F Y')?> for 
    <?php echo (($tier=='Tier 4') ? 'Post Tax':$tier)?> </h3>
    <hr noshade>
</div>

  <div>

  <h1 style="font-size:small">[<a id="emptbl-<?php echo $idx?>" class="e2e">Export</a>]</h1>
				
  <table id="e2e-emptbl-<?php echo $idx?>" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Name</th>
						<th>Employee</th>
						<th>Employer</th>
						<th>Redemption</th>
						<th>Total</th>
					</tr>
				</thead>
				<tbody>

                <?php foreach($ci as $i) { ?>
				<tr>
					<td><?php echo ucwords(strtolower($i['name'])) ?></td>
					<td><?php echo number_format($i['employee'],2) ?></td>
					<td><?php echo number_format($i['employer'],2) ?></td>
					<td><?php echo number_format($i['redemption'],2) ?></td>
					<td><?php echo number_format($i['total'],2) ?></td>
				</tr>
				<?php } ?> 
				</tbody>
			</table>
  </div> 
</div>
