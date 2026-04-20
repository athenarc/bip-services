<?php

namespace app\models;

use akiraz2\blog\Module;
use akiraz2\blog\traits\IActiveStatus;
use akiraz2\blog\traits\ModuleTrait;
use akiraz2\blog\traits\StatusTrait;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * Blog post without categories (BIP custom schema).
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $tags
 * @property string $slug
 * @property int $click
 * @property int|null $user_id
 * @property string|null $cover_image
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 */
class BlogPost extends ActiveRecord {
    use ModuleTrait;
    use StatusTrait;

    private $_status;
    /** @var UploadedFile|null */
    public $coverUpload;
    /** @var bool */
    public $removeCover = false;

    public static function tableName() {
        return '{{%blog_post}}';
    }

    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'title',
                'slugAttribute' => 'slug',
                'ensureUnique' => true,
            ],
            [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'user_id',
                ],
                'value' => static function () {
                    return Yii::$app->user->getId();
                },
            ],
        ];
    }

    public function rules() {
        return [
            [['title', 'content'], 'required'],
            [['click', 'user_id', 'status'], 'integer'],
            ['status', 'default', 'value' => IActiveStatus::STATUS_ACTIVE],
            ['status', 'in', 'range' => [
                IActiveStatus::STATUS_INACTIVE,
                IActiveStatus::STATUS_ACTIVE,
            ]],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['tags', 'slug'], 'string', 'max' => 128],
            ['tags', 'default', 'value' => ''],
            [['cover_image'], 'string', 'max' => 255],
            [['coverUpload'], 'image', 'extensions' => 'png, jpg, jpeg, webp', 'maxSize' => 5 * 1024 * 1024],
            [['removeCover'], 'boolean'],
            ['click', 'default', 'value' => 0],
        ];
    }

    public static function getStatusList() {
        return [
            IActiveStatus::STATUS_INACTIVE => 'Draft',
            IActiveStatus::STATUS_ACTIVE => 'Published',
        ];
    }

    public function attributeLabels() {
        return [
            'id' => Module::t('blog', 'ID'),
            'title' => Module::t('blog', 'Title'),
            'content' => Module::t('blog', 'Content'),
            'tags' => Module::t('blog', 'Tags'),
            'slug' => Module::t('blog', 'Slug'),
            'click' => Module::t('blog', 'Click'),
            'user_id' => Module::t('blog', 'User ID'),
            'cover_image' => 'Cover Image',
            'coverUpload' => 'Cover Image',
            'removeCover' => 'Remove current cover',
            'status' => Module::t('blog', 'Status'),
            'created_at' => Module::t('blog', 'Created At'),
            'updated_at' => Module::t('blog', 'Updated At'),
        ];
    }

    public function getUser() {
        if ($this->getModule()->userModel) {
            return $this->hasOne($this->getModule()->userModel::className(), [$this->getModule()->userPK => 'user_id']);
        }

        return null;
    }

    public function getUrl() {
        return Yii::$app->getUrlManager()->createUrl(['blog/default/view', 'id' => $this->id, 'slug' => $this->slug]);
    }

    public function getAbsoluteUrl() {
        return Yii::$app->getUrlManager()->createAbsoluteUrl(['blog/default/view', 'id' => $this->id, 'slug' => $this->slug]);
    }

    public function getCoverImageUrl(): ?string {
        if (empty($this->cover_image)) {
            return null;
        }

        return rtrim(Yii::$app->request->baseUrl, '/') . '/assets/blog-cover/' . $this->cover_image;
    }

    public function getTagItems(): array {
        if (trim((string) $this->tags) === '') {
            return [];
        }

        $items = array_map('trim', explode(',', (string) $this->tags));
        $items = array_filter($items, static function ($v) {
            return $v !== '';
        });

        return array_values(array_unique($items));
    }

    public function getTagLinks(): array {
        $links = [];
        foreach ($this->tagItems as $tag) {
            $normalized = mb_strtolower($tag, 'UTF-8');
            $display = mb_convert_case($normalized, MB_CASE_TITLE, 'UTF-8');
            $links[] = Html::a(
                Html::encode($display),
                ['/site/blog/' . rawurlencode($normalized)],
                ['class' => 'main-green']
            );
        }

        return $links;
    }

    public function getAuthorName(): string {
        $user = $this->user;
        if ($user && $user->researcher && trim((string) $user->researcher->name) !== '') {
            return trim((string) $user->researcher->name);
        }

        return 'unknown author';
    }

    public function getAuthorByline(): string {
        return 'Written by ' . $this->authorName;
    }

}
