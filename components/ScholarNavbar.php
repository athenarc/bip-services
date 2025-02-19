<?php

namespace app\components;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\bootstrap\Modal;

class ScholarNavbar extends Widget
{
    public $template;
    public $templateDropdownData;
    public $researcher;
    public $edit_perm;

    public function run() {

        ob_start(); // Start capturing output

        NavBar::begin([
            'options' => [
                'class' => 'navbar navbar-default second-navbar navbar-fixed-top', // Bootstrap 3 class
            ],
        ]);

        $leftMenuItems = [
            (!$this->template->isHidden())
                ?    [
                    'label' => '<span title="Choose a scholar profile to view or manage from the dropdown menu."><i class="fa fa-cubes" aria-hidden="true"></i> ' . $this->template->name . '</span>',
                    'items' => $this->getMenuItems(),
                    'encode' => false,
                ]
                : [
                    'label' => '<span title="This is a prototype scholar template">' . $this->template->name .
                        ' <span class="text-warning"><small>(prototype)</small></span></span>',
                    'encode' => false,
                ]
        ]; 

        // Info icon to trigger modal
        array_push($leftMenuItems, [
            'label' => '<span data-toggle="modal" data-target="#templateInfoModal" title="View template details">'
                . '<i class="fa fa-info-circle light-grey-link"></i> <span class="visible-xs-inline"> Template info</span></span>',
            'encode' => false,
        ]);

        // Left-aligned menu
        echo Nav::widget([
            'options' => ['id' => 'template-dropdown', 'class' => 'navbar-nav navbar-left align-items-center'],
            'items' => $leftMenuItems,
        ]);

        // Right-aligned menu
        echo Nav::widget([
            'options' => ['class' => 'nav navbar-nav navbar-right align-items-center'],
            'items' => [
                // Profile visibility toggle
                $this->edit_perm ? [
                    'label' => Html::tag('i', '', [
                        'id' => 'profile-visibility-toggle',
                        'class' => $this->researcher->is_public ? 'fas fa-lock-open light-grey-link' : 'fas fa-lock text-warning',
                        'title' => $this->researcher->is_public 
                            ? 'This profile is publicly visible (Switch to Private Profile).' 
                            : 'This profile is only visible to you (Switch to Public Profile).',
                        'data-toggle' => 'tooltip',
                    ]),
                    'encode' => false,
                    'options' => ['class' => 'navbar-icon'],
                ] : '',
                [
                    'label' => '<i class="fa fa-cog light-grey-link"></i> <span class="visible-xs-inline"> More options</span>',
                    'encode' => false,
                    'items' => [
                        [
                            'label' => '<i class="fa fa-gears"></i> Settings',
                            'url' => Url::to(['site/settings']),
                            'encode' => false,
                        ],
                        $this->edit_perm ? [
                            'label' => '<i class="fa fa-file-pdf"></i> Export PDF',
                            'url' => Url::to(['scholar/export-pdf', 'orcid' => $this->researcher->orcid, 'template_url_name' => $this->template->url_name]),
                            'encode' => false,
                            'linkOptions' => [
                                'id' => 'pdf-download-link',
                                'onclick' => 'animatePdfExportIcon(event);',
                            ],
                        ] : '',
                    ],
                ],
            ],
        ]);

        NavBar::end();

        echo $this->renderTemplateInfoModal();

        return ob_get_clean(); // Return the captured output
    }

    private function getMenuItems () {
        $menuItems = [];

        foreach ($this->templateDropdownData as $category) {
            $submenuItems = [];

            foreach ($category->templates as $template) {

                $url = Url::to(['scholar/profile/' . $this->researcher->orcid]) . '/' . $template->url_name;

                $submenuItems[] = [
                    'label' => Html::a($template->name, $url, [
                        'class' => 'btn btn-link dropdown-item highlight-subcategory',
                        'encode' => false,
                    ]),
                    'encode' => false,
                ];
            }

            if (!empty($submenuItems)) {
                $menuItems[] = [
                    'label' => $category->name,
                    'options' => ['class' => 'category-item'],
                    'items' => $submenuItems,
                    'encode' => false,
                ];
            }
        }

        return $menuItems;
    }

    private function renderTemplateInfoModal() {
        // Check if language and description are available, otherwise use 'Not available'
        $language = !empty($this->template->language) ? Html::encode(Yii::$app->params['languages'][$this->template->language]) : 'Not available';
        $description = !empty($this->template->description) ? Yii::$app->formatter->asHtml($this->template->description) : 'Not available';
    
    
        Modal::begin(['options' => ['class' => 'modal fade', 'id' => 'templateInfoModal'],
            'header' => '<h4>' . Html::encode($this->template->name) . '</h4>',
            'size' => 'modal-md',
        ]);
    
        // Generate the modal HTML
        ?>
    
            <p><strong>Language:</strong> <?= $language ?></p>
            <p><strong>Description:</strong> <?= $description ?></p>
                    
        <?php
        
        Modal::end();
    }

}
