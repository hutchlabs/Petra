<li class="active"><a href="#home" data-toggle="tab">Home</a></li>
 
<?php 
for($i=2; $i<5; $i++) { 
    $k = ($i==4) ? 'Post Tax' : 'Tier '.$i;	
    $funds = $k.' Funds';

    if ($emp->has_tier('Tier '.$i)) { 
		$id = '#tier'.$i;
?>

<li>
    <a href="<?php echo $id?>" data-toggle="tab"><?php echo $funds ?></a>
</li>
	   
<?php } else { ?>

<li>
    <a href="javacript:;" title="You have no <?php echo $funds?>" style="color:#aaa"><?php echo $funds?></a>
</li>
	
<?php }} ?>
