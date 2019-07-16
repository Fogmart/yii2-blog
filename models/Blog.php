<?php

namespace fogmart\blog\models;

use common\components\behaviors\StatusBehavior;
use common\models\ImageManager;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use yii\image\drivers\Image;
use yii\web\UploadedFile;

/**
 * This is the model class for table "blog".
 *
 * @property int $id
 * @property string $title
 * @property string $text
 * @property string $url
 * @property string $image
 * @property int $status_id
 * @property int $sort
 */
class Blog extends ActiveRecord
{
    const STATUS_LIST = ['off', 'on'];
    public $tags_arr;
    public $file;
    /**
     * {@inheritdoc}
     */



    public function behaviors()
    {
        return [
            'timestampBehavior' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'whncrt',
                'updatedAtAttribute' => 'whnupd',
                'value' => new Expression('NOW()'),
            ],
            'statusBehavior' => [
                'class' => StatusBehavior::className(),
                'statusList'=> self::STATUS_LIST
            ]
        ];
    }

    public static function tableName()
    {
        return 'blog';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'url'], 'required'],
            [['text'], 'string'],
            [['status_id', 'sort'], 'integer'],
            [['sort'], 'integer', 'max' => 99, 'min'=>1],
            [['title'], 'string', 'max' => 250],
            [['url'], 'string', 'max' => 255],
            [['image'], 'string', 'max' => 100],
            [['file'], 'image'],
            [['tags_arr', 'whnupd', 'whncrt'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'text' => 'Текст',
            'url' => 'УРЛ',
            'status_id' => 'Статус',
            'sort' => 'Сортировка',
            'tags_arr' => 'Teги',
            'whncrt' => 'Создано',
            'whnupd' => 'Обновлено',
            'image' => 'Картинка',
            'file' => 'Картинка',
        ];
    }

    public function getAuthor(){
        return $this->hasOne(User::className(), ['id'=>'user_id']);
    }

    public function getBlogTag(){
        return $this->hasMany(BlogTag::className(), ['blog_id'=>'id']);
    }

    public function getImages(){
        return $this->hasMany(ImageManager::className(), ['itm_id'=>'id'])
            ->andWhere(['class'=>self::tableName()] )->orderBy('sort');
    }

    public function getImagesLinks(){
        return ArrayHelper::getColumn($this->images, 'imageUrl');
    }

    public function getImageLinkData(){
        $arr = ArrayHelper::toArray($this->images,[
            ImageManager::className()=>[
                'caption'=>'name',
                'key'=>'id'
            ]]
        );
        return $arr;
    }

    public function getTags(){
        return $this->hasMany(Tag::className(), ['id'=>'tag_id'])->via('blogTag');
    }
    public function getSmallImage(){
        if ($this->image){
            $url  = 'http://yii.loc/uploads/images/blog/50x50/'.$this->image;
        } else{
            $url = 'http://yii.loc/uploads/images/nophoto.png';
        }
        return $url;
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->tags_arr = $this->tags;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $arr = ArrayHelper::map($this->tags, 'id', 'id');
        if (($this->tags_arr) ) {
            foreach ($this->tags_arr as $one) {
                if (!in_array($one, $arr)) {
                    $model = new BlogTag();
                    $model->tag_id = $one;
                    $model->blog_id = $this->id;
                    $model->save();
                }
                if (isset($arr[$one])) {
                    unset($arr[$one]);
                }
            }
        }

        BlogTag::deleteAll(['tag_id' => $arr]);
    }

    public function beforeSave($insert)
    {

        if ($file = UploadedFile::getInstance($this, 'file')){

            $dir = Yii::getAlias('@images').'/blog/';
            if ($this->image != '') {
                if (file_exists($dir . $this->image)) {
                    unlink($dir . $this->image);
                }
                if (file_exists($dir . '50x50/' . $this->image)) {
                    unlink($dir . '50x50/' . $this->image);
                }
                if (file_exists($dir . '800x/' . $this->image)) {
                    unlink($dir . '800x/' . $this->image);
                }
            }
            $this->image = strtotime('now').'_'.Yii::$app->getSecurity()->generateRandomString(6).'.'.
                $file->extension;
            $file->saveAs($dir.$this->image);
            $imag = Yii::$app->image->load($dir.$this->image);
            $imag->background('#fff', 0);
            $imag->resize('50', '50', Image::INVERSE);
            $imag->crop('50', '50');
            $imag->save($dir.'50x50/'.$this->image,90);

            $imag = Yii::$app->image->load($dir.$this->image);
            $imag->background('#fff', 0);
            $imag->resize('800', null, Image::INVERSE);
            $imag->save($dir.'800x/'.$this->image,90);

        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }
}
