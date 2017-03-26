<?php if(!class_exists('raintpl')){exit;}?><div class="form-group">
    <div class="forge-tournament bracket">
    <?php $counter1=-1; if( isset($encounterRounds) && is_array($encounterRounds) && sizeof($encounterRounds) ) foreach( $encounterRounds as $key1 => $value1 ){ $counter1++; ?>

        <div class="round round-<?php echo $value1["round"];?>">
            <h4><?php echo $value1["round"];?></h4>
            <?php $counter2=-1; if( isset($value1["encounters"]) && is_array($value1["encounters"]) && sizeof($value1["encounters"]) ) foreach( $value1["encounters"] as $key2 => $value2 ){ $counter2++; ?>

                <div class="encounter encounter-<?php echo $key2;?>">
                    <div class="team_1 <?php echo $value2["team_1"]["classes"];?>">
                        <span class="name"><?php echo $value2["team_1"]["name"];?></span>
                        <span class="score"><?php echo $value2["team_1"]["score"];?></span>
                        <a href="#" class="set_winner"></a>
                    </div>
                    <div class="team_2 <?php echo $value2["team_2"]["classes"];?>">
                        <span class="name"><?php echo $value2["team_2"]["name"];?></span>
                        <span class="score"><?php echo $value2["team_2"]["score"];?></span>
                        <a href="#" onclick="forgeTournament.setEncounterWinner(this, <?php echo $value2["team_2"]["id"];?>, 13, 0, 0)" class="set_winner"></a>
                    </div>
                </div>
            <?php } ?>

        </div>
    <?php } ?>

    </div>
</div>
