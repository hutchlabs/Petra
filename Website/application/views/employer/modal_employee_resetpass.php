<div id="myResetModal-<?php echo $m->id?>" class="modal rmodal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3 id="myModalLabel">Employee Account Reset</h3>
  </div>

  <div class="modal-body">
  <div id="notice-<?php echo $m->id?>"></div>
	<br/><br/>
	<div class="alert alert-block">
		<h4>Warning!</h4>
		<p>Reset account for <?php echo  ucwords(strtolower($m->name))?>?</p>
		<p>Employee will be required to sign up with a new password to access their information again.</p>
		<br/>
		<button type="button" id="<?php echo $m->id?>" class="resetpass btn btn-danger" data-loading-text="Reseting...">Go</button>
	</div>
  </div> 

  <div class="modal-footer">
    <button data-dismiss="modal" class="btn btn-primary">Close</button>
  </div>

</div>
