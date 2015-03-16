This kit is designed to automate routine processes of uploading files, their saving and storage.
It includes:
- File upload widget
- Component for storing files (built on top of flysystem)
- Actions to download, delete, and view (download) files
- Behavior for saving files in the model and delete files when you delete a model

# File Storage
To work with the File Kit you need to configure FileStorage first. This component is a layer of abstraction over the filesystem
- Its main task to take on the generation of a unique name for each file and trigger corresponding events.
```
'fileStorage'=>[
    'class' => 'trntv\filekit\Storage',
    'baseUrl' => '@web/uploads'
    'filesystem'=> ...
        // OR
    'filesystemComponent' => ...    
],
```
There are several ways to configure `Storage` to work with `flysystem`.

1. Create a builder class that implements `trntv\filekit\filesystem\FilesystemBuilderInterface` and implement method` build`
which returns filesystem object
Example:
```php
namespace app\components;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;
use trntv\filekit\filesystem\FilesystemBuilderInterface;

class LocalFlysystemBuilder implements FilesystemBuilderInterface
{
    public $path;

    public function build()
    {
        $adapter = new Local(\Yii::getAlias($this->path));
        return new Filesystem($adapter);
    }
}
```
Configuration:
```
'fileStorage'=>[
    ...
    'filesystem'=> [
        'class' => 'app\components\FilesystemBuilder',
        'foo' => 'bar'
        ...
    ]
]
```
Read more about flysystem at http://flysystem.thephpleague.com/

2. Use third-party extensions, `creocoder/yii2-flysystem` for example, and provide a name of the filesystem component in `filesystemComponent`
Configuration:
```
'fs' => [
    'class' => 'creocoder\flysystem\LocalFilesystem',
    'path' => '@webroot/files'
    ...
],
'fileStorage'=>[
    ...
    'filesystemComponent'=> 'fs'
],
```
# Actions
File Kit contains several Actions to work with uploads.

### Upload Action
Designed to save the file uploaded by the widget
```
public function actions(){
    return [
           'upload'=>[
               'class'=>'trntv\filekit\actions\UploadAction',
               'validationRules' => [
                    ...
               ],
               'on afterSave' => function($event) {
                    /* @var $file \League\Flysystem\File */
                    $file = $event->file
                    // do something (resize, add watermark etc)
               }
           ]
       ];
}
```
See additional settings in the corresponding class

### Delete Action
```
public function actions(){
    return [
       'delete'=>[
           'class'=>'trntv\filekit\actions\DeleteAction',
       ]
    ];
}
```
See additional settings in the corresponding class

### View (Donwload) Action
```
public function actions(){
    return [
       'view'=>[
           'class'=>'trntv\filekit\actions\ViewAction',
       ]
    ];
}
```
See additional settings in the corresponding class

# Upload Widget
Standalone usage
```
echo \trntv\filekit\widget\Upload::widget([
    'model'=>$model,
    'attribute'=>'files',
    'url'=>['upload'],
    'sortable'=>true,
    'maxFileSize'=>10 * 1024 * 1024, 
    'maxNumberOfFiles'=>3 // default 1
]);
```

With ActiveForm
```
echo $form->field($model, 'files')->widget(
    '\trntv\filekit\widget\Upload',
    [
        'url'=>['upload'],
        'sortable'=>true,
        'maxFileSize'=>10 * 1024 * 1024, // 10 MiB
        'maxNumberOfFiles'=>3 // default 1
    ]
);
```

# FilesBehavior
This behavior is designed to save uploaded files in the corresponding relation.

```php
 public function behaviors()
 {
     return [
          'file' => [
              'class' => 'trntv\filekit\behaviors\FilesBehavior',
              'multiple' => true,
              'attribute' => 'files',
              'filesRelation' => 'uploadedFiles',
              
          ],
      ];
 }
```
See additional settings in the corresponding class.
