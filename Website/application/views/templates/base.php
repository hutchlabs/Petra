<!DOCTYPE html>
<html lang="en">
<head>
<title>Petra Trust <?php echo ' - '. $title ?></title>
    <meta charset="utf-8">
	<meta name="google-site-verification" content="taTTx2XH4gs0hK8fFBRZU3myVVRUgeC0CW4RMbG7dqE" />	
    <meta name="description" content="Member Portal for Petra Trust">
    <meta name="author" content="David Hutchful">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="shortcut icon" href="/members/assets/img/favicon.ico" />

	<!--[if IE]>
	<link href='//fonts.googleapis.com/css?family=Open+Sans:400' rel='stylesheet' type='text/css'>
	<link href='//fonts.googleapis.com/css?family=Open+Sans:600' rel='stylesheet' type='text/css'>
	<link href='//fonts.googleapis.com/css?family=Open+Sans:700' rel='stylesheet' type='text/css'>
	<![endif]-->
	
    <link rel="stylesheet" href="/members/assets/css/style.css" />	
    <link rel="stylesheet" href="/members/assets/css/jquery-ui.css" type="text/css" /> 
	<?php if ($logged_in) { ?>
    <link rel="stylesheet" href="/members/assets/css/base-admin.css" />	
    <link rel="stylesheet" href="/members/assets/css/dashboard.css" />	
    <link rel="stylesheet" href="/members/assets/css/jquery.gritter.css" />	
	<link rel="stylesheet" href="/members/assets/css/Aristo.css" />
	<link rel="stylesheet" href="/members/assets/css/jquery.dataTables.css" />
	<link rel="stylesheet" href="/members/assets/css/jquery.dataTables_themeroller.css" />
	<?php } else { ?>
	<link rel="stylesheet" href="/members/assets/css/login.css" />
	<?php } ?>
</head>

<body>

<?php if ($logged_in) { ?>
<div id="siteWrapper">
	
<div id="siteHeader">
	<a id="siteLogo" href="/members/">Petra Trust</a>
				
	<ul id="siteNavigation">
		<li class="navItem home navItemOn"><a href="/members/">Home</a></li> 

        <?php if ($user->has_role('admin')) { ?>

        <li class="navItem navItemOn"><a href="/members/admin/employers">Employers</a></li> 
        <li class="navItem navItemOn"><a href="/members/admin/employees">Employees</a></li> 
        <li class="navItem navItemOn"><a href="/members/admin/reports">Reports</a></li> 


		<?php } elseif (!$user->has_role('admin')) { ?>
        
		<li class="navItem myProjects">
			<a href="#myBenefitsModal" data-toggle="modal">Benefits Calculator</a>
		</li>

		<li class="navItem menuNavItem myProjects">
				<a href="http://www.petratrust.com/resources">Downloads</a>
				<ul class="menu">
					<li class="menuCaption"></li>
					<li class="menuItem"> 
						<a href="http://www.petratrust.com/resources/insights">White Papers</a>
					</li>
					<li class="menuItem"> 
						<a href="http://www.petratrust.com/resources/related-links/">Forms</a>
					</li>
				</ul>
		</li>

		<?php } ?>
	</ul>

	<div id="userNavigation">
		<ul class="primaryNavigation">
			<li class="navItem">
				<a href="#" class="myAccount" title="My Information">
					<img src="/members/assets/img/user.png" alt="David avatar" class="avatar" />
					<div class="currentUserName">
					 Welcome <?php echo $user->username ?>
					</div>
				</a>
			</li>

			<li class="navItem">
				<a href="#" class="toggleUserSecondaryNavigation" title="Account Menu">
					<img src="/members/assets/img/ico_arrow_gry.png" class="ico_arrow_gry" />
				</a>
			</li>
		</ul>

		<ul class="secondaryNavigation">
			<li class="navItem">
				<a href="/members/user/change_password" class="change">Change Password</a>
				<a href="/members/user/logout" class="logout">Logout</a>
			</li>
		</ul>
	</div>
</div>

<div id="siteBody">
	<div id="sitePrimaryContent">
		<div class="contentHeader">
<?php } ?>
	
<?php echo $content; ?>

<?php if ($logged_in) { ?>
			<div class="clear"></div>
		</div>
	</div>
</div>



<!-- Benefits Calculator -->
<div id="myBenefitsModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 975px; margin-left:-488px;">

  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3 id="myModalLabel">Benefits Calculator</h3>
  </div>

  <div class="modal-body" style="margin-left: -20px">
	<span class="span6.5" style="padding-right: 20px; border-right:1px solid #eee;">

		<form name="form1" id="form1" method="post" action="" class="form-horizontal">
                        
		  <div class="control-group">
            <label class="control-label" for="cage">Current Age*</label>
			<div class="controls">
            	<input name="cage" type="text" class="span2 input-small" id="cage">
			</div>
		  </div>

		  <div class="control-group">
            <label class="control-label" for="rage">Retirement Age*</label>
			<div class="controls">
            	<input name="rage" type="text" class="span2" id="rage">
				<!--
				<span class="help-inline">&nbsp;Years to retire
					<input name="yretire" type="text" disabled="disabled" class="span1 spantext disabled" id="yretire" readonly>
				</span>
				-->
			</div>
		  </div>

		  <div class="control-group">
           	<label class="control-label" for="csal">Current Monthly Salary*</label>
			<div class="controls">
				<div class="input-prepend input-append">
  					<span class="add-on">GHC</span>
                    <input name="csal" type="text" class="span1" id="csal" placeholder="0" style="text-align: right">
				</div>
			</div>
		  </div>

		  <div class="control-group">
           	<label class="control-label" for="psal">Portion of Salary Invested</label>
			<div class="controls">
				<input name="psal" class="span1 input-small" type="text" id="psal" readonly>
				<span class="help-inline">
					<span id="psal_slider" class="span2"></span>
				</span>
			</div>
		  </div>

	  	  <div class="control-group">
           	<label class="control-label" for="pgro">Projected Average Annual Salary Growth</label>
			<div class="controls">
             	<input name="pgro"  class="span1 input-small" type="text" id="pgro" readonly>
				<span class="help-inline">
					<span id="pgro_slider" class="span2"></span>
				</span>
			</div>
		  </div>

	  	  <div class="control-group">
           	<label class="control-label" for="pret">Projected Average Investment Returns</label>
			<div class="controls">
             	<input name="pret"  class="span1 input-small" type="text" id="pret" readonly>
				<span class="help-inline">
					<span id="pret_slider" class="span2"></span>
				</span>
			</div>
		  </div>

	  	  <div class="control-group">
			<div class="controls">
               <input name="btn" type="button" class="btn btn-primary" id="btn" value="Calculate  &rsaquo;&rsaquo;  ">
			</div>
		  </div>
       </form>
	</span>

	<span class="span6" style="border:0px solid green;">
		<form name="form1" id="form1" method="post" action="" class="form-horizontal">
		  <div class="control-group">
           	<label class="control-label" for="acon">Monthly Contribution</label>
			<div class="controls">
				<div class="input-prepend input-append">
  					<span class="add-on">GHC</span>
                    <input name="acon" type="text" disabled="disabled" class="span2" id="acon" style="text-align:right" readonly>
				</div>
			</div>
		  </div>

		  <div class="control-group">
           	<label class="control-label" for="lumpsum">Lump Sum</label>
			<div class="controls">
				<div class="input-prepend input-append">
  					<span class="add-on">GHC</span>
                    <input name="lumpsum" type="text" disabled="disabled" class="span2" id="lumpsum" style="text-align:right" readonly>
				</div>
			</div>
		  </div>
		</form>

        <div id="results" style="display:none;">
          <div id="chartContainer">Loading...</div>
		</div>
	</span>
  </div>
  <div class="modal-footer">
    <button data-dismiss="modal" class="btn btn-primary">Close</button>
  </div>
</div>		
<!-- End calculator -->



<div id="siteFooter">
	<span class="copyright">
	&copy; 2012 Petra Trust. All Rights Reserved. Please call +233 (0) 302 74 0963/4 for help<br />
	</span>
</div>

</div>
<?php } ?>

    <script src="/members/assets/js/jquery.min.js"></script>
    <script src="/members/assets/js/jquery-ui.min.js"></script>
    <script src="/members/assets/js/jquery.validate.min.js"></script>
	<script src="/members/assets/js/jquery.stepy.min.js"></script>
	<script src="/members/assets/js/jquery.tablesorter.min.js"></script>
	<script src="/members/assets/js/jquery.gritter.js"></script>
	<script src="/members/assets/js/jquery.dataTables.min.js"></script>
	<script src="/members/assets/js/jquery.handsontable.full.js"></script>
	<script src="/members/assets/js/bootstrap.min.js"></script>

        <?php if ($logged_in) { ?>
        <?php if ($user->has_role('admin')) { ?>
        <script src="/members/assets/js/jspdf/jspdf.min.js"></script>
	    <script src="/members/assets/js/libs/Deflate/adler32cs.js"></script>
	    <script src="/members/assets/js/libs/FileSaverjs/FileSaver.js"></script>
	    <script src="/members/assets/js/libs/Blob.js/BlobBuilder.js"></script>
        <?php }} ?>

	<?php if ($logged_in) { ?>	
	<script src="/members/assets/js/highstock.js"></script>
	<script src="/members/assets/js/modules/exporting.js"></script>
    <script src="/members/assets/js/nav.js"></script>
<!--
	<script src="/members/assets/js/highcharts2.js"></script>
	<script src="/members/assets/js/accounting.min.js"></script>
	<script src="/members/assets/js/benefits.js"></script>
-->
    <?php } ?>
    <script src="/members/assets/js/script.js"></script>
	<script src="/members/assets/js/performance.js"></script>
</body>
</html>
