<?php if(!class_exists('raintpl')){exit;}?><section class="forge-tournament forge-tournament__detail">
    <div class="wrapped">
        <div class="info row">
            <div class="col-sm-6 text">
                <div class="head">
                    <div class="thumb" style="background-image: url('<?php echo $thumbnail;?>');"></div>
                    <h1><?php echo $title;?></h1>
                </div>
                <div class="forge-tournament-formular">
                    <form class="ajax" action="<?php echo $action;?>" callback="forgeTournament.formCallback">
                        <?php echo $form;?>

                    </form>
                </div>
            </div>
            <aside class="col-sm-6 quick">
                <div class="start">
                    <span class="title"><?php echo $starts_in_label;?></span>
                    <div><?php echo $remaining_time;?></div>
                </div>
                <div class="enrollment">
                    <span class="title"><?php echo $enrollment_label;?></span>
                    <div><?php echo $current_participants;?> / <?php echo $max_participants;?></div>
                </div>
            </aside>
        </div>
        <div class="enrollment">

        </div>
    </div>
</section>
