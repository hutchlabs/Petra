<h1 class="fltLeft">Beneficiaries</h1>

<br/><br/>
				
<div class="main">
  <div class="main-inner">
    <div class="container">
	<div class="row">

    <?php
        for($i=2; $i < 5; $i++) { 
		    if ($employee->has_tier('Tier '.$i)) {
                
                echo View::factory('employee/_benefit_share')
					    ->set('employee', $employee) 
					    ->set('beneficiaries', $beneficiaries) 
					    ->set('title', 'Tier '.$i); 
            }
        }
    ?>

	</div> <!-- /row -->
	</div> <!-- /container -->
   </div> <!-- /main-inner -->
</div> <!-- /main -->
