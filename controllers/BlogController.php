<?php

namespace app\controllers;

use akiraz2\blog\traits\IActiveStatus;
use akiraz2\blog\traits\ModuleTrait;
use app\models\AdminStats;
use app\models\BlogPost;
use app\models\BlogPostSearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Blog frontend (mounted as blog module controller id "default" — routes stay blog/default/...).
 */
class BlogController extends Controller {
    use ModuleTrait;

    public function beforeAction($action) {
        if (in_array($action->id, ['create', 'update', 'delete'], true) && ! AdminStats::hasAdminAccess()) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        return parent::beforeAction($action);
    }

    public function actions() {
        return [
            'captcha' => [
                'class' => 'lesha724\MathCaptcha\MathCaptchaAction',
            ],
        ];
    }

    public function actionIndex() {
        $searchModel = new BlogPostSearch();
        $searchModel->scenario = BlogPostSearch::SCENARIO_USER;
        $request = Yii::$app->request;
        $tagFromPath = trim((string) $request->get('tag', ''));

        if ($tagFromPath !== '' && $tagFromPath !== mb_strtolower($tagFromPath, 'UTF-8')) {
            $normalizedTag = mb_strtolower($tagFromPath, 'UTF-8');
            $params = $request->getQueryParams();
            unset($params['tag']);
            $query = http_build_query($params);
            $target = '/site/blog/' . rawurlencode($normalizedTag) . ($query !== '' ? '?' . $query : '');

            return $this->redirect($target, 301);
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cat_items' => [],
        ]);
    }

    public function actionView($id, $slug = null) {
        $post = BlogPost::find()->where(['status' => IActiveStatus::STATUS_ACTIVE, 'id' => $id])->one();

        if ($post === null) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        if ($slug !== $post->slug) {
            return $this->redirect($post->getUrl(), 301);
        }

        $post->updateCounters(['click' => 1]);

        $emptyComments = new ArrayDataProvider([
            'allModels' => [],
            'pagination' => false,
        ]);

        return $this->render('view', [
            'post' => $post,
            'dataProvider' => $emptyComments,
            'comment' => null,
        ]);
    }

    public function actionCreate() {
        $model = new BlogPost();
        $model->status = IActiveStatus::STATUS_ACTIVE;

        if ($model->load(Yii::$app->request->post())) {
            $model->coverUpload = UploadedFile::getInstance($model, 'coverUpload');

            if ($model->coverUpload !== null) {
                try {
                    $model->cover_image = $this->storeCoverImage($model->coverUpload);
                    // Tmp file has been moved; avoid re-validating coverUpload on save().
                    $model->coverUpload = null;
                } catch (\RuntimeException $e) {
                    $model->addError('coverUpload', 'Failed to upload cover image.');
                }
            }

            if (! $model->hasErrors() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Blog post created.');

                if ((int) $model->status === IActiveStatus::STATUS_ACTIVE) {
                    return $this->redirect(['view', 'id' => $model->id, 'slug' => $model->slug]);
                }

                return $this->redirect(['update', 'id' => $model->id]);
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id) {
        $model = BlogPost::findOne((int) $id);

        if ($model === null) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $oldCoverImage = $model->cover_image;
        $newCoverUploaded = false;
        $removeOldCover = false;

        if ($model->load(Yii::$app->request->post())) {
            $model->coverUpload = UploadedFile::getInstance($model, 'coverUpload');

            if ($model->coverUpload !== null) {
                try {
                    $model->cover_image = $this->storeCoverImage($model->coverUpload);
                    $newCoverUploaded = true;
                    $removeOldCover = ! empty($oldCoverImage);
                    // Tmp file has been moved; avoid re-validating coverUpload on save().
                    $model->coverUpload = null;
                } catch (\RuntimeException $e) {
                    $model->addError('coverUpload', 'Failed to upload cover image.');
                }
            } elseif ((bool) $model->removeCover && ! empty($oldCoverImage)) {
                $model->cover_image = null;
                $removeOldCover = true;
            }

            if (! $model->hasErrors() && $model->save()) {
                if ($removeOldCover) {
                    $oldPath = Yii::getAlias('@webroot/assets/blog-cover/') . $oldCoverImage;

                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                Yii::$app->session->setFlash('success', 'Blog post updated.');

                if ((int) $model->status === IActiveStatus::STATUS_ACTIVE) {
                    return $this->redirect(['view', 'id' => $model->id, 'slug' => $model->slug]);
                }

                return $this->redirect(['update', 'id' => $model->id]);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id) {
        if (! Yii::$app->request->isPost) {
            throw new MethodNotAllowedHttpException('Method not allowed.');
        }

        $model = BlogPost::findOne((int) $id);

        if ($model === null) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        if (! empty($model->cover_image)) {
            $coverPath = Yii::getAlias('@webroot/assets/blog-cover/') . $model->cover_image;

            if (is_file($coverPath)) {
                @unlink($coverPath);
            }
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Blog post deleted.');

        return $this->redirect(['index']);
    }

    private function storeCoverImage(UploadedFile $upload): string {
        $targetDir = Yii::getAlias('@webroot/assets/blog-cover');
        FileHelper::createDirectory($targetDir);

        $extension = strtolower((string) $upload->getExtension());
        $fileName = uniqid('cover_', true) . '.' . $extension;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        if (! $upload->saveAs($targetPath)) {
            throw new \RuntimeException('Could not save cover image.');
        }

        return $fileName;
    }
}
