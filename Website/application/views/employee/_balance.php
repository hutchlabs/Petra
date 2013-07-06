<?php
$tier2 = $tier3 = $tier4 = 0.00;
$t2d = $t3d = $t4d = date('Y-m-d');
$bal = $employee->balance();
foreach($bal as $tier => $info)
{
        if ($tier=='Tier 2')
        {
            $tier2 += $info['balance']; 
            $t2d = $info['date']; 
        }
        if ($tier=='Tier 3')
        {
            $tier3 += $info['balance']; 
            $t3d = $info['date']; 
        }
        if ($tier=='Tier 4')
        {
            $tier4 += $info['balance']; 
            $t4d = $info['date'];
        }
}
$tier2 = number_format($tier2,2);
$t2d = date_format(date_create($t2d),'jS F, Y');
$tier3 = number_format($tier3,2);
$t3d = date_format(date_create($t3d),'jS F, Y');
$tier4 = number_format($tier4,2);
$t4d = date_format(date_create($t4d),'jS F, Y');
?>
<div class="span12">
    <br><h1 class="fltLeft">Balance</h1><br/><br/>
    
    <div class="widget">
        <div class="widget-header">
            <i class="icon-signal"></i>
            <h3>Current</h3>
            <h3 style="float:right">
                [<a id="bal_tbl" class="e2e" style="font-size:small">Export</a>]
            </h3>
        </div> 

        <div class="widget-content">
            <table id="e2e-bal_tbl" 
                   class="table table-striped table-bordered paginate">
                <thead>
                    <tr>
                        <th>Tier</th>
                        <th style="text-align">
                            Tier 2 (balance as of: <?php echo $t2d ?>)
                        </th>
                        <th style="text-align:center">
                            Tier 3 (balance as of date: <?php echo $t3d ?>)
                        </th>
                        <th style="text-align:center">
                            Post Tax (balance as of: <?php echo $t4d?>)
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Balance</th>
                        <td style="text-align:center">
                            GHC <?php echo $tier2 ?>
                        </td>
                        <td style="text-align:center">
                            GHC <?php echo $tier3 ?>
                        </td>
                        <td style="text-align:center">
                            GHC <?php echo $tier4 ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div> 
