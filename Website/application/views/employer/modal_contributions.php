<div id="ContribModal-<?php echo $idx?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3 id="myModalLabel">Contributions for <?php echo date_format(date_create($date),'jS F Y')?></h3>
  </div>

  <div class="modal-body">

  <h1 style="font-size:small">[<a id="tbl_contrib-<?php echo $idx?>" class="e2e">Export</a>]</h1>
				
			<table id="e2e-tbl_contrib-<?php echo $idx?>" class="table table-striped table-bordered tablesorter paginated">
				<thead>
					<tr>
						<th>Name</th>
						<th>Employee</th>
						<th>Employer</th>
						<th>Total</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($employees as $m) { 
						foreach($m->dealinfo() as $deal) { 
						if ($deal['tier'] == $tier) {
							if ($deal['date'] == $date) {
							  if ($deal['scheme'] == $scheme) {
				?>
				<tr>
					<td><?php echo ucwords(strtolower($m->name)) ?></td>
					<td><?php echo number_format($deal['employee'],2) ?></td>
					<td><?php echo number_format($deal['employer'],2) ?></td>
					<td><?php echo number_format($deal['total'],2) ?></td>
				</tr>
				<?php }}}}} ?> 
				</tbody>
			</table>
  </div> 

  <div class="modal-footer">
    <button data-dismiss="modal" class="btn btn-primary">Close</button>
  </div>

</div>
