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
])
```
ACTIONS
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
PATH HELPER
-----------
```
$path = new \trntv\filekit\base\Path(['path'=>'/var/www/images/product.jpg'])
echo $path->filename; // product.js
$path->filename = 'service.jpg';
echo $path; // /var/www/images/service.jpg
$path->addFilenamePrefix('_thumb');
echo $path->filename; // service_thumb.jpg
echo $path; // /var/www/images/service_thumb.jpg
```
URL HELPER
-----------
```
$url = new \trntv\filekit\base\Url(['url'=>'http://example.com/1/test.php'])
echo $url->host; // example.com
echo $url->path->filename; // test.php;
$url->port = 88;
$url->path->filename = 'product.jpg';
echo $url; // http://example.com:88/1/product.jpg
$url->path->addFilenamePrefix('_thumb');
echo $url; // http://example.com:88/1/product_thumb.jpg
```

TODO
----
- MongoDB Repository
- Cloud repositories