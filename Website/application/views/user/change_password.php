
<?php if($errors): ?>
  	<?php foreach($errors as $field => $error): ?>
	  <p class="alert"><?php echo $error; ?></p>
	<?php endforeach; ?>
<?php endif; ?>

<h1 class="fltLeft">Change Password</h1>

<div id="user_form" class="content clearfix">

 <?php echo Form::open(); ?>


<div class="login-fields">

 <div class="control-group field">
    <label class="control-label" for="password">New Password:</label>
	<div class="controls">
		<input id="password" type="password" name="password" placeholder="Password" class="login"/>
  	</div>
  </div>

  <div class="control-group field">
    <label class="control-label" for="password_confirm">Confirm Password:</label>
	<div class="controls">
			<input id="password_confirm" type="password" name="password_confirm" placeholder="Confirm Password" class="login"/> 
  	</div>
  </div>

 <div class="control-group">
	<div class="controls">
		<input type="submit" class="btn" name="change_password" id="change_password" value="Change Password" />
    </div>
 </div>	
</div>

 </form>  
</div>
