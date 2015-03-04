extension is still under heavy development ;)
STORAGE
-------
```
'fileStorage'=>[
    'class'=>'trntv\filekit\storage\FileStorage',
    'repositories'=>[
        'uploads'=>[
            'class'=>'trntv\filekit\storage\repository\FilesystemRepository',
            'basePath'=>'@webroot/uploads',
            'baseUrl'=>'@web/uploads'
        ],
        'other-path'=>[
            'class'=>'trntv\filekit\storage\repository\FilesystemRepository',
            'basePath'=>'@webroot/other',
            'baseUrl'=>'@web/other',
            'createDbRecord'=>false
        ]
    ],
],
```
Don`t forget to apply included migration - m140805_084737_file_storage_item.php
```
php yii migrate --migrationPath=@trntv/filekit/migrations
```

`` \Yii::$app->fileStorage->getRepository('uploads')->save(UploadedFile::getInstanceByName('image')); ``
`` \Yii::$app->fileStorage->saveAll(UploadedFile::getInstancesByName('files')); ``
`` \Yii::$app->fileStorage->save('http://external_host.com/file.pdf', 'awesome documents', 'other-path'); ``

WIDGET
------
```
echo \trntv\filekit\widget\Upload::widget([
    'model'=>$model,
    'attribute'=>'files',
    'url'=>['upload'],
    'sortable'=>true,
    'fileuploadOptions'=>[
        'maxFileSize'=>10000000, // 10 MiB
        'maxNumberOfFiles'=>3
    ]
]);

echo $form->field($model, 'files')->widget(
    '\trntv\filekit\widget\Upload',
    [
        'url'=>['upload'],
        'sortable'=>true,
        'fileuploadOptions'=>[
            'maxFileSize'=>10000000, // 10 MiB
            'maxNumberOfFiles'=>3
        ]
    ]
);
```

SomeModel.php
```
public $files = [];
```

ACTION
-------
```
public function actions(){
   return [
           'upload'=>[
               'class'=>'trntv\filekit\actions\UploadAction',
               'responseUrlParam'=>'file-url',
               'fileProcessing'=>function($file, $uploadAction){
                    // do something (resize, add watermark etc)
               }
           ]
       ];
   }
```
BEHAVIOR
--------
```php
 public function behaviors()
 {
     return [
          'file' => [
              'class' => 'trntv\filekit\behaviors\UploadBehavior',
              'uploadAttribute' => 'file',
              'resultAttribute' => 'path',
              'fileCategory' => 'products',
              'fileRepository' => 'uploads',
              'fileProcessing'=>function($file, $uploadAction){
                  // resize etc
              }
          ],
      ];
 }
```

TODO
----
- MongoDB Repository
- Cloud repositories
