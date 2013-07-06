<h1 class="fltLeft"><?php echo $ptitle ?></h1>

<br/><br/>
				
<div class="main">
  <div class="main-inner">
    <div class="container">
	<div class="row">

    <div class="widget widget-table action-table">

    <div class="widget-header">
      <i class="icon-th-list"></i>
      <h3>Employers</h3>
    </div> 

    <div class="widget-content">
       <table id="managertbl" class="table table-striped table-bordered table-hover table-condensed paginated tablesorter" data="100">
	        <thead>
			    <tr>
				    <th>Name</th>
				    <th>Type</th>
				    <th>Account Info</th>
				    <th class="td-actions">Signup Status</th>
			    </tr>
	        </thead>
	        <tbody>

		        <?php foreach ($employers as $m) { ?>
			    <tr>
					<td><?php echo ucwords(strtolower($m->name)) ?></td>
					<td><?php echo $m->description ?></td>
                    <td>
					    <a href="view?type=employer&eid=<?php echo $m->id?>" role="button" class="btn btn-small">View account</a>
					<td>
					<?php if (is_numeric($m->user->id)) { ?>
					<a href="#myResetModal-<?php echo $m->id?>" role="button" class="btn btn-small btn-danger" data-toggle="modal">Reset user account</a>
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

<?php 
foreach ($employers as $m) { 
   echo View::factory('employer/modal_employee_resetpass')->bind('m', $m);
}
?>
	</div> <!-- /row -->
	</div> <!-- /container -->
   </div> <!-- /main-inner -->
</div> <!-- /main -->

