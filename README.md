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
            'baseUrl'=>'@web/uploads',
        ]
    ],
],
```
Don`t forget to apply included migration - m140805_084737_file_storage_item.php

WIDGET
------
```
echo \trntv\filekit\widget\Upload::widget([
    'model'=>$model,
    'attribute'=>'files',
    'url'=>['upload'],
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