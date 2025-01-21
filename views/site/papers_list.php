<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\BookmarkIcon;
use app\components\ImpactIcons;
?>


<?php if(isset($warning)) { ?>
  <div class="alert alert-warning">
    <center><b>Note:</b> <?= $warning ?></center>
  </div>

<?php } ?>

<table class="table table-hover">

  <tbody>
    <?php foreach($papers as $paper){ ?>
        <tr>
            <!-- <?= empty(trim($paper['authors'])) ? "N/A" : trim($paper['authors']) ?>, -->

            <td style="width:83%">
              <div>
                <b>
                  <a href="<?= yii\helpers\Url::to(['site/details', 'id'=> $paper['doi']]) ?>" target="_blank" class='main-green' title = 'Show details'>
                  <?= empty(trim($paper['title'])) ? "N/A" : trim($paper['title']) ?> <i class="fa fa-info-circle" aria-hidden="true"></i></a>
                </b>
              </div>
              <div>
                <i><?= empty(trim($paper['journal'])) ? "N/A" : trim($paper['journal']) ?></i> &middot;
                <i><?= (empty($paper['year']) || $paper['year'] == 0) ? "N/A" : $paper['year'] ?></i> &middot;
                <a href="https://doi.org/<?= $paper['doi'] ?>" target='_blank' class="grey-link"><?= $paper['doi'] ?> <i class="fa fa-external-link-square" aria-hidden="true"></i></a> 
                <i><?= !empty($paper['relation_name']) ? "&middot; " . $paper['relation_name'] : "" ?></i>
              </div>
            </td>

            <td style = "text-align:right;">

              <!-- impact -->
              <?= ImpactIcons::widget([
                                      'impact_indicators' => $impact_indicators,
                                      'popularity_class' => $paper['pop_class'],
                                      'influence_class' => $paper['inf_class'],
                                      'impulse_class' => $paper['imp_class'],
                                      'cc_class' => $paper['cc_class'],
                                      'popularity_score' => $paper['attrank'],
                                      'influence_score' => $paper['pagerank'],
                                      'impulse_score' => $paper['3y_cc'],
                                      'cc_score' => $paper['citation_count'],
                                      ]);?>

            </td>
            <td style = "text-align:right;">
              <!-- bookmark -->
              <?= BookmarkIcon::widget(['user_liked' => $paper['user_id'],
                                        'user_logged' => Yii::$app->user->id,
                                        'id_bookmark' => $paper['internal_id']]);?>
            </td>
        </tr>
    <?php } ?>
  </tbody>
</table>

