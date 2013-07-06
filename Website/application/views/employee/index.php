<h1 class="fltLeft">Welcome <?php echo $employee->name?></h1>

<br/><br/>
				
<div class="main">
  <div class="main-inner">
    <div class="container">
	<div class="row">

        <ul class="nav nav-tabs">
        <?php 
            echo View::factory('employee/_tabs')->set('emp', $employee); 
        ?> 
        </ul>

        <div class="tab-content">
  
            <!-- Home tab -->
            <div class="tab-pane active" id="home">
                <?php 
                   echo View::factory('employee/_balance')
                             ->set('employee', $employee); 

                    echo View::factory('employee/_balance_history')
                             ->set('clist', $clist) 
                             ->set('employee', $employee); 
                ?> 
            </div>

            <!-- Tabs for Tiers 2-4 tab -->
            <?php 
                for($i=2; $i < 5; $i++) { 
		            if ($employee->has_tier('Tier '.$i)) {
            ?>

            <div class="tab-pane" id="tier<?php echo $i?>">
		        <?php 
			        echo View::factory('employee/_contributions')
					    ->set('dealinfo', $dealinfo) 
					    ->set('tier3b', $tier3b) 
					    ->set('tier', 'Tier '.$i); 

			        echo View::factory('employee/_fundclosing')
					        ->set('clist', $clist) 
					        ->set('tier', 'Tier '.$i); 
		         ?>
            </div>
            <?php }} ?>

        </div>

	</div> <!-- /row -->
	</div> <!-- /container -->
   </div> <!-- /main-inner -->
</div> <!-- /main -->
