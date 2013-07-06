<h1 class="login"><a href="http://www.petratrust.com/">Petra Trust</a></h1>

<form action="/members/user/signup" method="post" accept-charset="utf-8" id="register-form">

	<h3>Create Your Account</h3>

	<?php if($errors): ?>
	<div class="error">
  		<?php foreach($errors as $field => $error): ?>
		  <p class="alert"><?php echo $error; ?></p>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<fieldset title="Step 1">
		<div class="entry clear introtext alert">
			<b>Step 1 of 3:</b> 
			Please select the account type and provide
            the necessary company id or phone number to proceed. 
		</div>

		<!-- Account type -->
 		<div class="entry clear">
			<label class="header" for="account_type">Account Type:</label>
			<div class="controls">
				<select id="account_type" name="account_type">
					<option value="manager">Employer account</option>
					<option value="employee">Employee account</option>
				</select>
  			</div>
  		</div>

		<!-- Employer Id type -->
 		<div class="entry clear mgr_elem">
			<label class="header" for="company_id">Employer ID:</label>
			<div class="controls">
				<input id="company_id" type="text" name="company_id" 
					   placeholder="Enter Employer ID" />
  			</div>
  		</div>
 
		<!-- Employee phone number -->
		<div class="entry clear emp_elem">
    		<label class="header" for="phone_number">Phone Number:</label>
			<div class="controls">
				<input id="phone_number" type="text" name="phone_number" 
					   placeholder="e.g. 0209959580" />
			</div>
		</div> 

		
		<div class="entry clear emp_elem">
			<label class="header" style="margin-left:140px; font-size: small; font-weight:bold">-- OR --</label>
		</div>

		<div class="entry clear emp_elem">
    		<label class="header" for="email">Email:</label>
			<div class="controls">
				<input id="email" type="email" name="email" 
					   placeholder="e.g. company@client.com" />
  			</div>
  		</div>
	</fieldset>

	<fieldset title="Step 2">
		<div id="code_intro" class="entry clear introtext alert">
			<b>Step 2 of 3:</b> 
			Please select the account type and provide
            the necessary company id or phone number to proceed. 
		</div>

 		<div id="code" class="entry clear">
    		<label class="header" for="code">Code:</label>
			<div class="controls">
				<input id="code" type="text" name="code" 
					   placeholder="Registration code" />
  			</div>
  		</div>
	</fieldset>

	<fieldset title="Step 3">
		<div id="code_intro" class="entry clear introtext alert">
			<b>Step 3 of 3:</b> 
			Please select a username and password to use for this
            account. 
		</div>
	
	  	<div class="entry clear">
    		<label class="header" for="username">Username:</label>
			<div class="controls">
				<input id="username" type="text" name="username" 
						placeholder="Username" />
    		</div>
  		</div>

  		<div class="entry clear">
    		<label class="header" for="password">Password:</label>
			<div class="controls">
				<input id="password" type="password" name="password" 
							placeholder="Password" />
  			</div>
  		</div>

  		<div class="entry clear">
			<label class="header" for="password_confirm">
				Confirm Password:
			</label>
			<div class="controls">
			  <input id="password_confirm" type="password" 
				name="password_confirm" placeholder="Confirm Password" /> 
  			</div>
  		</div>
	</fieldset>

	<input type="submit" class="finish btn btn-primary btn-large" 
		   name="register" id="register" value="Sign Up" />
 </form> 

	<div class="clear">
		<div class="noAccount" style="margin-top: -30px; margin-left:-40px; font-size:small">
			Already have an account? 
			<a href="/members/user/signin">Sign In</a> <br/>
            If you have any issues, please call +233 (0) 302 74 0963/4 for help
		</div> 
	</div>	

