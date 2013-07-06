<li class="active"><a href="#home" data-toggle="tab">Home</a></li>
<?php 
foreach($tiers as $k => $exists) { 
		$k = ($k=='Tier 4') ? 'Post Tax' : $k;
        $funds = $k.' Funds';
        
        if ($exists) { 
		    $id = '#'.preg_replace('/ /','',strtolower($k));
		    $id = preg_replace('/posttax/','tier4',$id);
?>
<li>
  <a href="<?php echo $id ?>" data-toggle="tab"><?php echo $funds ?></a>
</li>

<?php } else { ?>

<li>
   <a href="javacript:;" title="You have no <?php echo $funds?>" style="color:#aaa"><?php echo $funds?></a>
</li>

<?php }} ?>

<li style="float:right">
   <a href="#employees" data-toggle="tab">Employees</a>
</li> 
