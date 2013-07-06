<div class="span12">
<br><h1 class="fltLeft"><?php echo $title ?></h1><br/><br/>
    
    <div class="widget">
        <div class="widget-header">
            <i class="icon-signal"></i>
            <h3>Beneficiaries</h3>
            <h3 style="float:right">
                [<a id="bal_tbl" class="e2e" style="font-size:small">Export</a>]
            </h3>
        </div> 

        <div class="widget-content">
            <table id="e2e-bal_tbl" 
                   class="table table-striped table-bordered paginate">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th style="text-align:center">Share (%)</th>
                        <th style="text-align:center">Amount</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!sizeof($beneficiaries)) { ?>
                    <tr><td colspan="3">No beneficiaries found</td></tr>

                <?php  } else { foreach($beneficiaries as $b) { ?>
                    <tr>
                        <td><?php echo $b->name?></th>
                        <td><?php echo $b->share?></th>
                        <td><?php echo $b->amount?></th>
                    </tr>
                 <?php }} ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 
