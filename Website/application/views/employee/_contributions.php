<div class="span12">

<?php if ($tier=='Tier 3') { ?>
<div class="widget">
		<div class="widget-header">
			<i class="icon-signal"></i>
			<h3>Current Tier 3 Fund Value</h3>
		</div> 

		<div class="widget-content">
			<div class="alert alert-info" style="font-size: 18px;">
				As of today, if you cashed out your Tier 3 funds, it will be worth GHC 
                <span id="tier3v"><b><?php echo number_format($tier3b * 0.75,2) ?></b></span>
			</div>
			<p>Tax Rate: <b><span id="taxrate">25%</span></b></p>
			<div id="tax-slider" style="width: 250px"></div>
			<input type="hidden" id="fvalue" value="<?php echo $tier3b; ?>" />
		</div> 
</div>
<?php } ?>

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
						<th>Total</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($dealinfo as $deal) { 
                        $date = date_format(date_create($deal['date']),
                            'jS F Y');

                        $emplee = number_format($deal['employee'],2);
                        $empler = number_format($deal['employer'],2);
					    $total = number_format($deal['total'],2);

						if($deal['tier'] == $tier) {
				?>
				<tr>
					<td><?php echo $date ?></td>
					<td><?php echo $deal['scheme'] ?></td>
					<td><?php echo $emplee ?></td>
					<td><?php echo $empler ?></td>
					<td><?php echo $total ?></td>
				</tr>
				<?php }} ?> 
				</tbody>
			</table>
		</div> 
</div>
</div>
