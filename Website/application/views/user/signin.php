
	<h1 class="login"><a href="http://www.petratrust.com/">Petra Trust</a></h1>

 	<form action="/members/user" method="post" accept-charset="utf-8">

		<h3>Sign In</h3>

		<?php if($error): ?>
		<div class="error">
  			<p class="alert">Your username or password is incorrect.</p>
		</div>
		<?php endif; ?>
			
		<div class="entry username-entry clear">
			<label class="header" for="username">Username </label>
			<input type="text" name="username" class="username" placeholder="enter your username" tabindex="1" />
			
	
		</div>
			
		<div class="entry password-entry clear">
			<label class="header" for="password">Password</label>
			<input type="password" name="password" class="password" placeholder="enter your password" tabindex="2" />
			<!--
			<a href="/forgot_password" class="forgotPassword">Forgot Password</a>
			-->
		</div>

  		<div class="entry" style="margin-left: 170px;">
    		<input class="btn btn-primary btn-large" type="submit" name="login" tabindex="4" value="Sign In" />
			<label class="rememberMe" for="remember">
				<input type="checkbox" name="remember" value="1" tabindex="3"  />
				Keep me signed in
			</label>
		</div> 
	</form>
	
	<div class="entry clear">
		<div class="noAccount" style="margin-top: -30px; margin-left:-40px; font-size:small">
		Don't have an account? <a href="/members/user/signup">Sign Up</a><br/>
            If you have forgotten your password, please call +233 (0) 302 74 0963/4 for help
		</div>
	</div>
