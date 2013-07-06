<h1 class="fltLeft"><?php echo $ptitle ?></h1>

<br/><br/>
				
<div class="main">
  <div class="main-inner">
    <div class="container">
	<div class="row">

         <label for="fn_company">Company:</label>
         <select id="fn_company" style="width: 400px;" name="fn_company">
         <?php 
             foreach($coms as $cid => $info) {
                    echo '<option value="'.$cid.'">'.$info['name'].'</option>';
             } 
         ?> 
         </select>
        
        <br/><br/>
        
         <?php foreach($coms as $cid => $info) { ?>

             <div id="section_<?php echo $cid ?>" class="fn_sections" style="display:none">
               <table class="table table-striped table-bordered table-hover table-condensed">
                 <thead>
                    <tr>
                        <th>Scheme</th>
                        <th>Display Name</th>
                        <th>Action</th>
                    </tr>
                 </thead>
                 <tbody>

                 <?php foreach($info['funds'] as $fid => $fund) { ?>               
                    <tr>
                        <td style="width: 30%; font-size:9px;"><?php echo $fund['name'] ?></td>  
                        <td>
                            <input type="text" value="<?php echo $fund['display'] ?>" id="fninput_<?php echo $cid.'-'.$fid ?>" style="font-size: small;width: 80%" />
                        </td> 
                        <td style="width:10%;">
                            <a id="<?php echo $cid.'-'.$fid?>" role="button" class="btn btn-small fnupdater">Update info</a>
                        </td>
                    </tr> 
                <?php } ?>
                </tbody>
              </table>
            </div>

        <?php } ?> 

	</div> <!-- /row -->
	</div> <!-- /container -->
   </div> <!-- /main-inner -->
</div> <!-- /main -->
