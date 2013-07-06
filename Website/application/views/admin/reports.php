<h1 class="fltLeft"><?php echo $ptitle ?></h1>

<br/><br/>
				
<div class="main">
  <div class="main-inner">
    <div class="container">
	<div class="row">

    <div>
       <table border="0">
            <tr>
            <td style="padding: 0 20px 20px 20px; vertical-align:top">

        <form method="post" id="reporter" accept-charset="utf-8"> 

          <table>
            <tbody>
              <tr>
                <td style="text-align:right" >
                    <label for="adm_employer_id">Company:</label>
                </td>
                <td>
                    <select id="adm_employer_id" style="width: 400px;" name="adm_employer_id">
                    <?php 
                        foreach($employers as $key=> $val) {
                        echo '<option value="'.$key.'">'.$val.'</option>';
                        } 
                    ?> 
                    </select>
                </td>
              </tr>
              <tr>
                <td style="text-align:right; vertical-align:top" >
                    <label for="adm_schemes_id">Scheme:</label>
                </td>
                <td>
                    <ul id="adm_schemes_id" style="width: 400px;"></ul>
                    <button type="button" class="choose" name="sselectall" id="sselectall" style="font-size:small">Select all</button>
                    <button type="button" class="choose" name="sdeselectall" id="sdeselectall" style="font-size:small">De-select all</button>
                    <br/><br/>
                </td>
              </tr>

              <tr>
                <td style="text-align:right; vertical-align:top" >
                    <label for="adm_holders_id">Employees:</label>
                </td>
                <td>
                    <ul id="adm_holders_id" style="width: 400px;"></ul>
                    <button type="button" class="choose" name="selectall" id="selectall" style="font-size:small">Select all</button>
                    <button type="button" class="choose" name="deselectall" id="deselectall" style="font-size:small">De-select all</button>
                    <br/><br/>
                </td>
               </tr>

                <tr>
                    <td style="text-align:right; vertical-align:top" >
                        <label for="adm_sdatepicker">Start Date:</label>
                    </td>
                    <td>
                        <input type="text" class="selector" name="sd" id="adm_sdatepicker" value="<?php echo $sd ?>">
                    </td>
                 </tr>

                <tr>
                    <td style="text-align:right; vertical-align:top" >
                        <label for="adm_edatepicker">End Date:</label>
                    </td>
                    <td>
                        <input type="text" class="selector" name="ed" id="adm_edatepicker" value="<?php echo $ed ?>">
                    </td>
                </tr>

                <tr>
                    <td style="text-align:right; vertical-align:top" >
                        &nbsp; 
                    </td>
                    <td>
                        <input type="submit" id="adm_submit1" name="submit" class="btn" value="Download Report">&nbsp;&nbsp;
                        <input type="submit" id="adm_submit2" name="submit" class="btn" value="Email Manager">&nbsp;&nbsp;
                        <input type="submit" id="adm_submit3" name="submit" class="btn" value="Email Clients">&nbsp;&nbsp;
                    </td>
                </tr>
            </tbody>
          </table>
        </form>
        </td>

            <td>&nbsp;</td> 
          <td style="padding: 0px 60px 20px 20px;vertical-align:top">
            <fieldset style="border: 0px solid black">
                <label><b>Sample output:</b></label>
                <iframe id="preview-pane" class="preview-pane" width="100%" height="500" frameborder="1"></iframe>
                <a href="#" id="adm_preview" class="btn btn-primary">Preview</a>
                
            </fieldset>
          </td>
        </tr>
        </tbody>
        </table>
    </div>

	</div> <!-- /row -->
	</div> <!-- /container -->
   </div> <!-- /main-inner -->
</div> <!-- /main -->

