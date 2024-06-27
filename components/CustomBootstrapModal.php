<?php

/*
 * Custom bootstrap model used to load content with ajax for references/citations
 *
 * author: @Serafeim
 */

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class CustomBootstrapModal extends Widget
{
    public $id;

    public function init()
    {
        parent::init();

        $js_code = <<<JS
        $(document).on('show.bs.modal', '#{$this->id}', function(e) {
          let link = $(e.relatedTarget);
          $(this).find("#{$this->id}-title").html(link.attr("modal-title"));

          $.ajax({
            url: link.attr("href"),
            success: (result) => {
              if (result.search("DOI Not Found") != -1) {
                result = "An error has occurred while fetching BibTex for this article";
              }

              $(this).find("#{$this->id}-contents").html(result);
              StartPopover();
            },
            error: () => {
              $(this).find("#{$this->id}-contents").html("An error has occurred while fetching papers from the database, please try again");
            }
          });
        });

        $(document).on('hidden.bs.modal', '#{$this->id}', function(e) {
          $(this).find("#{$this->id}-contents").html("<center><i class=\"fa fa-spinner fa-pulse fa-3x fa-fw\"></i><br/><br/>Loading (it may take a couple of seconds)...</center>");
        });
JS;

        echo Html::script($js_code, ['type'=>'text/javascript']) ;
    }

    public function run()
    {
        return $this->render('custom_modal', [
            'id' => $this->id,
        ]);
    }
}

?>